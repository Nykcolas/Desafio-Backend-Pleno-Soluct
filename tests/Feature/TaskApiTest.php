<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use App\Enums\TaskStatus;
use Laravel\Sanctum\Sanctum;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_um_usuario_autenticado_pode_criar_uma_tarefa(): void
    {
        $taskData = [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => TaskStatus::PENDING->value,
            'due_date' => now()->addWeek()->format('Y-m-d'),
        ];
        $response = $this->postJson('/api/v1/tasks', $taskData);
        $response->assertStatus(201)
            ->assertJsonFragment(['title' => $taskData['title']]);
        $this->assertDatabaseHas('tasks', [
            'title' => $taskData['title'],
            'user_id' => $this->user->id,
        ]);
    }

    public function test_um_usuario_nao_pode_ver_a_tarefa_de_outro_usuario(): void
    {
        $anotherUser = User::factory()->create();
        $taskFromAnotherUser = Task::factory()->create(['user_id' => $anotherUser->id]);
        $response = $this->getJson('/api/v1/tasks/' . $taskFromAnotherUser->id);
        $response->assertStatus(404);
    }

    public function test_o_proprietario_pode_atualizar_sua_tarefa_e_o_historico_e_criado(): void
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Título Antigo',
            'status' => TaskStatus::PENDING->value,
        ]);
        $updateData = [
            'title' => 'Título Novo Atualizado',
            'status' => TaskStatus::COMPLETED->value,
        ];
        $response = $this->putJson('/api/v1/tasks/' . $task->id, $updateData);
        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Título Novo Atualizado']);
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Novo Atualizado',
        ]);
        $this->assertDatabaseHas('task_histories', [
            'task_id' => $task->id,
            'field_changed' => 'title',
        ]);
        $this->assertDatabaseHas('task_histories', [
            'task_id' => $task->id,
            'field_changed' => 'status',
        ]);
    }

    public function test_o_proprietario_pode_deletar_sua_tarefa(): void
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $response = $this->deleteJson('/api/v1/tasks/' . $task->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
