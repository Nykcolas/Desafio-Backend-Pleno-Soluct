<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskHistoryResource;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Traits\FiltrableTrait;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="TaskHistory",
 *     description="Endpoints para histórico de alterações das tarefas"
 * )
 *
 * @OA\Get(
 *     path="/api/v1/tasks/{task}/history",
 *     summary="Listar o histórico de alterações de uma tarefa",
 *     description="Retorna uma lista paginada de alterações feitas na tarefa especificada. Suporta filtros, ordenação e paginação.
 *     
 * **Exemplo de Uso Completo:**
 * `/api/v1/tasks/1/history?filters[field_changed][operator]=like&filters[field_changed][value]=status&filters[changed_at][operator]=between&filters[changed_at][value]=2025-01-01,2025-06-30&sort_by=changed_at&sort_order=desc&page=1`",
 *     tags={"TaskHistory"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="task",
 *         in="path",
 *         required=true,
 *         description="ID da tarefa cujos históricos serão retornados",
 *         @OA\Schema(type="integer")
 *     ),
 *
 *     @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         required=false,
 *         description="Número de itens por página.",
 *         @OA\Schema(type="integer", default=15, maximum=500)
 *     ),
 *     @OA\Parameter(
 *         name="page",
 *         in="query",
 *         required=false,
 *         description="Número da página.",
 *         @OA\Schema(type="integer", default=1, maximum=100)
 *     ),
 *     @OA\Parameter(
 *         name="sort_by",
 *         in="query",
 *         required=false,
 *         description="Campo para ordenação. Colunas permitidas: field_changed, old_value, new_value, changed_at.",
 *         @OA\Schema(type="string", default="changed_at")
 *     ),
 *     @OA\Parameter(
 *         name="sort_order",
 *         in="query",
 *         required=false,
 *         description="Ordem da ordenação.",
 *         @OA\Schema(type="string", enum={"asc", "desc"}, default="desc")
 *     ),
 *
 *     @OA\Parameter(
 *         name="filters[field_changed][operator]",
 *         in="query",
 *         required=false,
 *         description="Operador para o filtro do campo modificado.",
 *         @OA\Schema(type="string", enum={"like"})
 *     ),
 *     @OA\Parameter(
 *         name="filters[field_changed][value]",
 *         in="query",
 *         required=false,
 *         description="Valor para o campo 'field_changed'.",
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Parameter(
 *         name="filters[changed_at][operator]",
 *         in="query",
 *         required=false,
 *         description="Operador para o filtro de data de alteração.",
 *         @OA\Schema(type="string", enum={"=", ">=", "<=", "between"})
 *     ),
 *     @OA\Parameter(
 *         name="filters[changed_at][value]",
 *         in="query",
 *         required=false,
 *         description="Data ou intervalo de datas. Para 'between', use formato: YYYY-MM-DD,YYYY-MM-DD",
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Histórico da tarefa listado com sucesso",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="current_page", type="integer", example=1),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="task_id", type="integer", example=1),
 *                     @OA\Property(property="field_changed", type="string", example="status"),
 *                     @OA\Property(property="old_value", type="string", example="pending"),
 *                     @OA\Property(property="new_value", type="string", example="completed"),
 *                     @OA\Property(property="changed_at", type="string", format="date-time", example="2025-06-25T14:00:00Z"),
 *                     @OA\Property(property="user_id", type="integer", example=2),
 *                     @OA\Property(property="created_at", type="string", format="date-time"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time")
 *                 )
 *             ),
 *             @OA\Property(property="first_page_url", type="string", example="http://localhost/api/v1/tasks/1/history?page=1"),
 *             @OA\Property(property="last_page", type="integer", example=5),
 *             @OA\Property(property="total", type="integer", example=45),
 *             @OA\Property(property="per_page", type="integer", example=15),
 *             @OA\Property(property="path", type="string", example="http://localhost/api/v1/tasks/1/history"),
 *             @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost/api/v1/tasks/1/history?page=2"),
 *             @OA\Property(property="prev_page_url", type="string", nullable=true, example=null)
 *         )
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
 *         description="Erro de validação nos filtros"
 *     )
 * )
 */
class TaskHistoryController extends Controller
{
    use FiltrableTrait {
        index as indexFiltrable;
    }
    
    protected $model = TaskHistory::class;
    protected $resourceClass = TaskHistoryResource::class;

    protected $sortable = [
        'field_changed',
        'old_value',
        'new_value',
        'changed_at',
    ];

    protected $filterable = [
        'field_changed' => [
            'type' => 'string',
            'operator' => 'like',
        ],
        'changed_at' => [
            'type' => 'date',
            'operator' => '=',
        ],
    ];

    public function index(Request $request, Task $task)
    {
        return $this->indexFiltrable($request, ['task_id' => $task->id]);
    }
}
