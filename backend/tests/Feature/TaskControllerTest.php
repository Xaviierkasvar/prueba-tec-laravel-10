<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear un usuario de prueba y obtener token
        $this->user = User::factory()->create();
        $this->token = auth()->login($this->user);
    }

    protected function headers()
    {
        return ['Authorization' => 'Bearer ' . $this->token];
    }

    public function test_user_can_get_their_tasks()
    {
        // Crear algunas tareas para el usuario
        Task::factory()->count(3)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->headers())
            ->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'title', 'description', 'status', 'created_at', 'updated_at']
                ]
            ]);
    }

    public function test_user_can_create_task()
    {
        $taskData = [
            'title' => 'Nueva Tarea',
            'description' => 'Descripción de la tarea',
            'status' => 'active'
        ];

        $response = $this->withHeaders($this->headers())
            ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tarea creada exitosamente'
            ])
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'title', 'description', 'status']
            ]);
    }

    public function test_user_cannot_create_task_with_invalid_data()
    {
        $response = $this->withHeaders($this->headers())
            ->postJson('/api/tasks', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    public function test_user_can_view_specific_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->headers())
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => ['id', 'title', 'description', 'status']
            ]);
    }

    public function test_user_cannot_view_others_tasks()
    {
        $otherUser = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $otherUser->id
        ]);

        $response = $this->withHeaders($this->headers())
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(404);
    }

    public function test_user_can_update_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $updateData = [
            'title' => 'Título Actualizado',
            'description' => 'Descripción actualizada',
            'status' => 'inactive'
        ];

        $response = $this->withHeaders($this->headers())
            ->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tarea actualizada exitosamente'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Título Actualizado'
        ]);
    }

    public function test_user_can_delete_task()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->headers())
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Tarea eliminada exitosamente'
            ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id
        ]);
    }

    public function test_user_can_toggle_task_status()
    {
        $task = Task::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'active'
        ]);

        $response = $this->withHeaders($this->headers())
            ->putJson("/api/tasks/{$task->id}/toggle-status"); // Cambiado a putJson y ajustada la URL

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Estado de la tarea actualizado exitosamente'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'inactive'
        ]);
    }
}