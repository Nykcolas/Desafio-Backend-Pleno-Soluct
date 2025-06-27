<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

trait FiltrableTrait
{
    public function index(Request $request, array $fixedFilters = [])
    {
        try {
            $query = $this->model::query();

            foreach ($fixedFilters as $field => $value) {
                $query->where($field, $value);
            }

            if (in_array('user_id', (new $this->model)->getFillable())) {
                $query->where('user_id', auth()->id());
            }

            if ($request->has('with')) {
                $with = array_map('trim', explode(',', $request->query('with')));
                $query->with($with);
            }

            $filters = $request->query('filters', []);
            if (is_string($filters)) {
                $filters = json_decode($filters, true) ?? [];
            }

            $this->applyFilters($query, $filters);

            $sortBy = $request->query('sort_by', 'id');
            $sortOrder = $request->query('sort_order', 'asc');
            $this->applySorting($query, $sortBy, $sortOrder);

            $perPage = (int) $request->query('per_page', 15);
            $results = $query->paginate(min($perPage, 500));

            return $this->resourceClass
                ? $this->resourceClass::collection($results)
                : $results;
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno ao processar a requisição.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function applyFilters(Builder $query, array $filters)
    {
        $allowedFilters = $this->filterable ?? [];
        $errors = [];

        foreach ($filters as $field => $filterDetails) {
            if (!isset($allowedFilters[$field])) {
                $errors[$field] = 'Campo de filtro não permitido.';
                continue;
            }

            $filterConfig = $allowedFilters[$field];
            $allowedOperators = $filterConfig['operator'] ?? ['='];
            $type = $filterConfig['type'] ?? 'string';

            $operator = strtolower($filterDetails['operator'] ?? '=');
            $value = $filterDetails['value'] ?? null;

            if (!in_array($operator, $allowedOperators)) {
                $errors[$field] = "Operador '$operator' não permitido para o campo.";
                continue;
            }

            if (!$this->isValidType($value, $type, $operator, $filterConfig)) {
                $errors[$field] = "Valor inválido para o tipo '$type'.";
                continue;
            }

            $this->applyFilterCondition($query, $field, $operator, $value);
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(['filters' => $errors]);
        }
    }

    protected function applyFilterCondition(Builder $query, string $field, string $operator, $value)
    {
        switch ($operator) {
            case 'between':
                if (is_string($value)) {
                    $value = explode(',', $value);
                }
                $query->whereBetween($field, [$value[0], $value[1]]);
                break;

            case 'in':
                $value = is_string($value) ? explode(',', $value) : (array) $value;
                $query->whereIn($field, $value);
                break;

            case 'notin':
                $value = is_string($value) ? explode(',', $value) : (array) $value;
                $query->whereNotIn($field, $value);
                break;

            case 'null':
                $query->whereNull($field);
                break;

            case 'notnull':
                $query->whereNotNull($field);
                break;

            case 'ornull':
                $query->where(function (Builder $q) use ($field, $value) {
                    $q->where($field, $value)->orWhereNull($field);
                });
                break;

            case 'like':
                $query->where($field, 'like', "%$value%");
                break;

            default:
                $query->where($field, $operator, $value);
                break;
        }
    }

    protected function isValidType($value, string $type, string $operator, array $config): bool
    {
        if (in_array($operator, ['null', 'notnull'])) {
            return true;
        }

        switch ($type) {
            case 'string':
                return is_string($value);

            case 'enum':
                return isset($config['enum']) && in_array($value, $config['enum']);

            case 'date':
                if ($operator === 'between') {
                    if (is_string($value)) {
                        $value = explode(',', $value);
                    }
                    if (!is_array($value) || count($value) !== 2) return false;
                    return $this->isValidDate($value[0]) && $this->isValidDate($value[1]);
                }
                return $this->isValidDate($value);

            default:
                return true;
        }
    }

    protected function isValidDate($date): bool
    {
        try {
            Carbon::parse($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function applySorting(Builder $query, string $sortBy, string $sortOrder)
    {
        $allowedSorts = $this->sortable ?? [];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }
    }
}
