<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="Desafio Backend Pleno - Soluct API",
 * description="Documentação da API RESTful para o desafio técnico da Soluct.
 * * **Observações Importantes:**
 * - Todas as requisições que enviam dados no corpo (POST, PUT, PATCH) devem usar o header `Content-Type: application/json`.
 * - Todas as requisições devem incluir o header `Accept: application/json` para garantir que a resposta seja recebida no formato JSON correto.
 * - Os endpoints que requerem autenticação estão marcados com um ícone de cadeado.",
 * @OA\Contact(
 * email="elizaelnycolas@gmail.com"
 * )
 * )
 */
abstract class Controller
{
}
