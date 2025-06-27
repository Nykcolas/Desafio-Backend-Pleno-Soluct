<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskHistoryResource;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Traits\FiltrableTrait;
use Illuminate\Http\Request;

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
