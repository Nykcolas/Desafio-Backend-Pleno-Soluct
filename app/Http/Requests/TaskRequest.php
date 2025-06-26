<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\TaskStatus;
use Illuminate\Validation\Rule;
use App\Models\Task;

class TaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $taskId = $this->route('task');
            $task = Task::find($taskId);

            return $task && $this->user()->id == $task->user_id;
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(TaskStatus::values())],
            'due_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título da tarefa é obrigatório.',
            'title.max' => 'O título da tarefa não pode exceder 255 caracteres.',
            'status.required' => 'O status da tarefa é obrigatório.',
            'status.in' => 'O status fornecido é inválido. Valores aceitos: ' . implode(', ', TaskStatus::values()),
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
        ];
    }
}
