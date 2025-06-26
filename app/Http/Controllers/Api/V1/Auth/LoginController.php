<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Post(
 *     path="/api/v1/login",
 *     summary="Realiza o login do usuário",
 *     description="Use um dos seguintes usuários de teste com a senha `senha123`:
 * 
 * - joao.silva@example.com
 * - maria.oliveira@example.com
 * - carlos.santos@example.com
 * - ana.pereira@example.com
 * - lucas.almeida@example.com
 * ",
 *     tags={"Autenticação"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email","password"},
 *             @OA\Property(property="email", type="string", format="email", example="joao.silva@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="senha123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login realizado com sucesso.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Login realizado com sucesso."),
 *             @OA\Property(property="access_token", type="string", example="1|abc123..."),
 *             @OA\Property(property="token_type", type="string", example="Bearer"),
 *             @OA\Property(property="user", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Credenciais inválidas.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Credenciais inválidas.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro interno ao tentar realizar o login.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ocorreu um erro ao tentar realizar o login."),
 *             @OA\Property(property="error", type="string", example="Mensagem de erro detalhada")
 *         )
 *     )
 * )
 *
 * @OA\Post(
 *     path="/api/v1/logout",
 *     summary="Realiza o logout do usuário autenticado",
 *     tags={"Autenticação"},
 *     security={{"sanctum":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Logout realizado com sucesso.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Não foi possível realizar o logout.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Não foi possível realizar o logout.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro interno ao tentar realizar o logout.",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Ocorreu um erro ao tentar realizar o logout."),
 *             @OA\Property(property="error", type="string", example="Mensagem de erro detalhada")
 *         )
 *     )
 * )
 */

class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'message' => 'Credenciais inválidas.',
                ], 401);
            }

            $user = $request->user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login realizado com sucesso.',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Ocorreu um erro ao tentar realizar o login.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user || !$user->currentAccessToken()) {
                return response()->json([
                    'message' => 'Não foi possível realizar o logout.',
                ], 400);
            }

            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout realizado com sucesso.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Ocorreu um erro ao tentar realizar o logout.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
