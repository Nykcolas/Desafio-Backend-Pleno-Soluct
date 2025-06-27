<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class UserController extends Controller
{
/**
 * @OA\Get(
 *     path="/api/v1/me",
 *     summary="Obter informações do usuário autenticado",
 *     description="Retorna os dados do usuário atualmente autenticado.",
 *     tags={"Usuário"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Dados do usuário autenticado",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Nicolas Araujo"),
 *             @OA\Property(property="email", type="string", example="nicolas@example.com"),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-25T20:00:00Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-25T20:30:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Usuário não autenticado"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro interno ao buscar usuário"
 *     )
 * )
 */
    public function me(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Usuário não autenticado.'], 401);
            }
            return $user->toResource();
        } catch (Exception $e) {
            Log::error('Erro ao buscar usuário autenticado: ' . $e->getMessage());
            return response()->json(['error' => 'Erro interno ao buscar usuário.'], 500);
        }
    }

    /**
 * @OA\Delete(
 *     path="/api/v1/me",
 *     summary="Excluir conta do usuário autenticado",
 *     description="Exclui a conta do usuário autenticado e revoga todos os tokens.",
 *     tags={"Usuário"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Conta excluída com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Conta excluída com sucesso.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Usuário não autenticado"
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro interno ao excluir conta"
 *     )
 * )
 */


    public function destroy(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'Usuário não autenticado.'], 401);
            }
            $user->tokens()->delete();
            $user->delete();

            return response()->json([
                'message' => 'Conta excluída com sucesso.',
            ]);
        } catch (Exception $e) {
            Log::error('Erro ao excluir conta: ' . $e->getMessage());
            return response()->json([
                'error' => 'Erro interno ao excluir conta.',
            ], 500);
        }
    }

    /**
 * @OA\Put(
 *     path="/api/v1/me",
 *     summary="Atualizar dados do usuário autenticado",
 *     description="Atualiza os dados do usuário autenticado. Permite alterar nome, email e senha.",
 *     tags={"Usuário"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", maxLength=255, example="Nome do Usuário"),
 *             @OA\Property(property="email", type="string", format="email", maxLength=255, example="usuario@email.com"),
 *             @OA\Property(property="password", type="string", minLength=8, example="novaSenha123"),
 *             @OA\Property(property="password_confirmation", type="string", minLength=8, example="novaSenha123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Usuário atualizado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Usuário atualizado com sucesso."),
 *             @OA\Property(property="user", type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Nome do Usuário"),
 *                 @OA\Property(property="email", type="string", example="usuario@email.com"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-06-25T20:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-06-25T20:30:00Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erro de validação."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro ao atualizar usuário"
 *     )
 * )
 */

    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ]);

            if (isset($validated['name'])) {
                $user->name = $validated['name'];
            }

            if (isset($validated['email'])) {
                $user->email = $validated['email'];
            }

            if (!empty($validated['password'])) {
                $user->password = bcrypt($validated['password']);
            }

            $user->save();

            return $user->toResource()
                ->additional(['message' => 'Usuário atualizado com sucesso.']);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erro ao atualizar usuário.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}