<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

trait FiltrableTrait
{
    public function index(Request $request)
    {
        try {
            $query = $this->model::where('user_id', auth()->id());

            if ($request->has('with')) {
                $with = array_map('trim', explode(',', $request->query('with')));
                $query->with($with);
            }

            $filters = $request->query('filters', []);
            $this->applyFilters($query, $filters);

            $sortBy = $request->query('sort_by', 'id');
            $sortOrder = $request->query('sort_order', 'asc');
            $this->applySorting($query, $sortBy, $sortOrder);

            $perPage = (int) $request->query('per_page', 15);
            $results = $query->paginate(min($perPage, 500));

            return response()->json($results);
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

        foreach ($filters as $field => $filterDetails) {
            if (!in_array($field, $allowedFilters)) {
                continue;
            }

            $operator = $filterDetails['operator'] ?? '=';
            $value = $filterDetails['value'] ?? null;

            switch (strtolower($operator)) {
                case 'between':
                    if (is_string($value)) {
                        $dates = explode(',', $value);
                        if (count($dates) === 2) {
                            $query->whereBetween($field, [$dates[0], $dates[1]]);
                        }
                    }
                    break;

                case 'ornull':
                    $query->where(function (Builder $q) use ($field, $value) {
                        $q->where($field, $value)->orWhereNull($field);
                    });
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
                default:
                    if ($value !== null) {
                        $query->where($field, $operator, $value);
                    }
                    break;
            }
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
