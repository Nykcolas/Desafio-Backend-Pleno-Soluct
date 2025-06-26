<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskHistory;
use App\Jobs\SendTaskWebhookJob;
class TaskObserver
{
    public function created(Task $task): void
    {
        $history = TaskHistory::create([
            'task_id' => $task->id,
            'user_id' => $task->user_id,
            'field_changed' => 'created',
            'old_value' => null,
            'new_value' => $task->title,
        ]);

        SendTaskWebhookJob::dispatch($history);
    }

    public function updating(Task $task): void
    {
        foreach ($task->getDirty() as $field => $newValue) {
            if ($field === 'updated_at') {
                continue;
            }

            $oldValue = $task->getOriginal($field);

            $oldValueForLog = is_object($oldValue) && enum_exists(get_class($oldValue)) ? $oldValue->value : $oldValue;
            $newValueForLog = is_object($newValue) && enum_exists(get_class($newValue)) ? $newValue->value : $newValue;

            $history = TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(),
                'field_changed' => $field,
                'old_value' => $oldValueForLog,
                'new_value' => $newValueForLog,
            ]);

            SendTaskWebhookJob::dispatch($history);
        }
    }
}
