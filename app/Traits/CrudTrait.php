<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

trait CrudTrait
{
    public function store(Request $request)
    {
        try {
            $data = $this->validateRequest($request);
            $data['user_id'] = auth()->id();
            $item = $this->model::create($data);

            return response()->json([
                'message' => 'Registro criado com sucesso.',
                'data' => $item,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar registro.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $item = $this->findAndCheckOwnership($id);
            return response()->json($item);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Registro não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao buscar registro.', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $item = $this->findAndCheckOwnership($id);
            $data = $this->validateRequest($request);
            $item->update($data);

            return response()->json([
                'message' => 'Registro atualizado com sucesso.',
                'data' => $item,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Registro não encontrado.'], 404);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Erro de validação.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar registro.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = $this->findAndCheckOwnership($id);
            $item->delete();
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Registro não encontrado.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao deletar registro.', 'error' => $e->getMessage()], 500);
        }
    }

    private function findAndCheckOwnership($id)
    {
        $relationName = (new $this->model)->getTable();
        return auth()->user()->{$relationName}()->findOrFail($id);
    }

    protected function validateRequest(Request $request)
    {
        if (isset($this->requestClass)) {
            $formRequest = app($this->requestClass);
            $formRequest->merge($request->all());
            $formRequest->setContainer(app())->setRedirector(app('redirect'));
            $formRequest->validateResolved();
            return $formRequest->validated();
        }
        return $request->all();
    }
}
