<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\CrudTrait;
use App\Traits\FiltrableTrait;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="Endpoints para gerenciamento de tarefas"
 * )
 *
 * @OA\Get(
 * path="/api/v1/tasks",
 * summary="Listar as tarefas do usuário",
 * description="Retorna uma lista paginada de tarefas pertencentes ao usuário autenticado. Este endpoint suporta filtros complexos, ordenação e paginação.
 *
 * **Exemplo de Uso Completo:**
 * Para buscar tarefas com status 'pending' ou 'in_progress', com data de vencimento entre 2025-01-01 e 2025-01-31, ordenadas pela data de vencimento (da mais antiga para a mais nova), a URL seria:
 * `/api/v1/tasks?filters[status][operator]=in&filters[status][value]=pending,in_progress&filters[due_date][operator]=between&filters[due_date][value]=2025-01-01,2025-01-31&sort_by=due_date&sort_order=asc&page=1`",
 * tags={"Tasks"},
 * security={{"bearerAuth":{}}},
 * @OA\Parameter(
 * name="per_page",
 * in="query",
 * required=false,
 * description="Número de itens por página.",
 * @OA\Schema(type="integer", default=15, maximum=500)
 * ),
 * @OA\Parameter(
 * name="page",
 * in="query",
 * required=false,
 * description="Número da página.",
 * @OA\Schema(type="integer", default=1, maximum=100)
 * ),
 * @OA\Parameter(
 * name="sort_by",
 * in="query",
 * required=false,
 * description="Campo para ordenação. Colunas permitidas: id, title, status, due_date, created_at.",
 * @OA\Schema(type="string", default="created_at")
 * ),
 * @OA\Parameter(
 * name="sort_order",
 * in="query",
 * required=false,
 * description="Ordem da ordenação.",
 * @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
 * ),
 * @OA\Parameter(
 * name="filters[status][value]",
 * in="query",
 * required=false,
 * description="Filtra tarefas por status. Para múltiplos status, separe por vírgula (ex: pendente,em_andamento).",
 * @OA\Schema(type="string")
 * ),
 * @OA\Parameter(
 * name="filters[status][operator]",
 * in="query",
 * required=false,
 * description="Operador para o filtro de status.",
 * @OA\Schema(type="string", default="=", enum={"=", "in"})
 * ),
 * @OA\Parameter(
 * name="filters[title][value]",
 * in="query",
 * required=false,
 * description="Filtra tarefas por título (busca parcial).",
 * @OA\Schema(type="string")
 * ),
 * @OA\Parameter(
 * name="filters[title][operator]",
 * in="query",
 * required=false,
 * description="Operador para o filtro de título.",
 * @OA\Schema(type="string", default="like", enum={"like", "="})
 * ),
 * @OA\Parameter(
 * name="filters[due_date][value]",
 * in="query",
 * required=false,
 * description="Filtra por data de vencimento. Para o operador 'between', use duas datas separadas por vírgula (YYYY-MM-DD,YYYY-MM-DD).",
 * @OA\Schema(type="string")
 * ),
 * @OA\Parameter(
 * name="filters[due_date][operator]",
 * in="query",
 * required=false,
 * description="Operador para o filtro de data de vencimento.",
 * @OA\Schema(type="string", default="=", enum={"=", ">", "<", ">=", "<=", "between"})
 * ),
 * @OA\Response(
 * response=200,
 * description="Operação bem-sucedida. Retorna a lista paginada de tarefas.",
 * @OA\JsonContent(
 * type="object",
 * @OA\Property(property="current_page", type="integer", example=1),
 * @OA\Property(
 * property="data",
 * type="array",
 * @OA\Items(
 * type="object",
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="user_id", type="integer", example=1),
 * @OA\Property(property="title", type="string", example="Título da Tarefa"),
 * @OA\Property(property="description", type="string", example="Descrição da Tarefa"),
 * @OA\Property(property="status", type="string", example="pending"),
 * @OA\Property(property="due_date", type="string", format="date", example="2025-12-31"),
 * @OA\Property(property="created_at", type="string", format="date-time"),
 * @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 * ),
 * @OA\Property(property="first_page_url", type="string", example="http://localhost/api/v1/tasks?page=1"),
 * @OA\Property(property="from", type="integer", example=1),
 * @OA\Property(property="last_page", type="integer", example=5),
 * @OA\Property(property="last_page_url", type="string", example="http://localhost/api/v1/tasks?page=5"),
 * @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost/api/v1/tasks?page=2"),
 * @OA\Property(property="path", type="string", example="http://localhost/api/v1/tasks"),
 * @OA\Property(property="per_page", type="integer", example=15),
 * @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
 * @OA\Property(property="to", type="integer", example=15),
 * @OA\Property(property="total", type="integer", example=75)
 * )
 * ),
 * @OA\Response(
 * response=401,
 * description="Não autorizado"
 * )
 * )
 *
 * @OA\Post(
 *     path="/api/v1/tasks",
 *     summary="Cria uma nova tarefa",
 *     tags={"Tasks"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         description="Dados da tarefa",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "canceled"}),
 *             @OA\Property(property="due_date", type="string", format="date")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Tarefa criada com sucesso"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação"
 *     )
 * )
 *
 * @OA\Get(
 *     path="/api/v1/tasks/{id}",
 *     summary="Exibe uma tarefa",
 *     tags={"Tasks"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID da tarefa",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Detalhes da tarefa"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Tarefa não encontrada"
 *     )
 * )
 *
 * @OA\Put(
 *     path="/api/v1/tasks/{id}",
 *     summary="Atualiza uma tarefa",
 *     tags={"Tasks"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID da tarefa",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         description="Dados para atualizar",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="status", type="enumeration", enum={"pending", "in_progress", "completed"}),
 *             @OA\Property(property="due_date", type="string", format="date")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Tarefa atualizada com sucesso"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Tarefa não encontrada"
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Erro de validação"
 *     )
 * )
 *
 * @OA\Delete(
 *     path="/api/v1/tasks/{id}",
 *     summary="Deleta uma tarefa",
 *     tags={"Tasks"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="ID da tarefa",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=204,
 *         description="Tarefa deletada com sucesso"
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Não autorizado"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Tarefa não encontrada"
 *     )
 * )
 */
class TaskController extends Controller
{
    use CrudTrait, FiltrableTrait;

    protected $model = Task::class;
    protected $requestClass = TaskRequest::class;
    protected $resourceClass = TaskResource::class;

    protected $sortable = [
        'id',
        'title',
        'status',
        'due_date',
        'created_at',
    ];

    protected $filterable = [
        'title' => [
            'operator' => ['like', '='],
            'type' => 'string',
        ],
        'status' => [
            'operator' => ['='],
            'type' => 'enum',
            'enum' => ['pending', 'in_progress', 'completed', 'canceled'],
        ],
        'due_date' => [
            'operator' => ['=', '>=', '<=', '>', '<', 'between'],
            'type' => 'date',
        ],
    ];
}
