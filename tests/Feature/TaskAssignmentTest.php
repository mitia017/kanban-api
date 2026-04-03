<?php

namespace Tests\Feature;

use App\Models\Column;
use App\Models\Kanban;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'user']);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    public function test_user_can_login()
    {
        User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'user']);
    }

    public function test_admin_can_assign_task()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $kanban = Kanban::create(['title' => 'Project 1']);
        $column = Column::create(['kanban_id' => $kanban->id, 'title' => 'To Do', 'order' => 1]);
        $task = Task::create(['column_id' => $column->id, 'title' => 'Fix Bug', 'priority' => 'high']);

        $response = $this->actingAs($admin)
            ->postJson("/api/tasks/{$task->id}/assign", [
                'assigned_to' => $user->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.assigned_to.id', $user->id);

        $this->assertEquals($user->id, $task->fresh()->assigned_to);
    }

    public function test_user_cannot_assign_task()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);
        $kanban = Kanban::create(['title' => 'Project 1']);
        $column = Column::create(['kanban_id' => $kanban->id, 'title' => 'To Do', 'order' => 1]);
        $task = Task::create(['column_id' => $column->id, 'title' => 'Fix Bug', 'priority' => 'high']);

        $response = $this->actingAs($user1)
            ->postJson("/api/tasks/{$task->id}/assign", [
                'assigned_to' => $user2->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_can_only_see_their_tasks()
    {
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $kanban = Kanban::create(['title' => 'Project 1']);
        $column = Column::create(['kanban_id' => $kanban->id, 'title' => 'To Do', 'order' => 1]);

        $task1 = Task::create(['column_id' => $column->id, 'title' => 'Task 1', 'assigned_to' => $user1->id, 'priority' => 'medium']);
        $task2 = Task::create(['column_id' => $column->id, 'title' => 'Task 2', 'assigned_to' => $user2->id, 'priority' => 'medium']);

        $response = $this->actingAs($user1)
            ->getJson("/api/columns/{$column->id}/tasks");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $task1->id);
    }

    public function test_admin_can_see_all_tasks()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $kanban = Kanban::create(['title' => 'Project 1']);
        $column = Column::create(['kanban_id' => $kanban->id, 'title' => 'To Do', 'order' => 1]);

        Task::create(['column_id' => $column->id, 'title' => 'Task 1', 'assigned_to' => $user1->id, 'priority' => 'medium']);
        Task::create(['column_id' => $column->id, 'title' => 'Task 2', 'assigned_to' => $user2->id, 'priority' => 'medium']);

        $response = $this->actingAs($admin)
            ->getJson("/api/columns/{$column->id}/tasks");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
