<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    /**
     * @OA\Post(
     * path="/api/v1/register",
     * summary="Registrar um novo usuário",
     * description="Cria um novo perfil de usuário. A senha deve ter no mínimo 8 caracteres e ser enviada com um campo de confirmação.",
     * tags={"Usuário"},
     * @OA\Parameter(
     * name="Accept",
     * in="header",
     * required=true,
     * description="Define o tipo de conteúdo aceito na resposta.",
     * @OA\Schema(
     * type="string",
     * default="application/json"
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Dados do usuário para o registro.",
     * @OA\JsonContent(
     * required={"name", "email", "password", "password_confirmation"},
     * @OA\Property(property="name", type="string", description="Nome completo do usuário.", example="João da Silva"),
     * @OA\Property(property="email", type="string", format="email", description="Endereço de e-mail do usuário.", example="joao@email.com"),
     * @OA\Property(property="password", type="string", format="password", description="Senha com no mínimo 8 caracteres.", example="senhaForte123"),
     * @OA\Property(property="password_confirmation", type="string", format="password", description="Confirmação da senha.", example="senhaForte123")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Usuário registrado com sucesso.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Usuário registrado com sucesso."),
     * @OA\Property(property="data", type="object",
     * @OA\Property(property="id", type="integer", example=1),
     * @OA\Property(property="name", type="string", example="João da Silva"),
     * @OA\Property(property="email", type="string", format="email", example="joao@email.com")
     * )
     * )
     * ),
     * @OA\Response(
     * response=422,
     * description="Erro de validação nos dados enviados.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="The given data was invalid."),
     * @OA\Property(property="errors", type="object",
     * @OA\Property(
     * property="email",
     * type="array",
     * @OA\Items(type="string", example="Este e-mail já está em uso.")
     * ),
     * @OA\Property(
     * property="password",
     * type="array",
     * @OA\Items(type="string", example="A confirmação da senha não confere.")
     * )
     * )
     * )
     * ),
     * @OA\Response(
     * response=500,
     * description="Erro interno do servidor.",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Erro ao registrar usuário.")
     * )
     * )
     * )
     */
    public function store(RegisterUserRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => 'Usuário registrado com sucesso.',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao registrar usuário.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
