<?php

namespace Tests\Feature;

use App\Models\Kanban;
use App\Models\User;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_their_own_kanbans()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Kanban::create(['title' => 'User 1 Board', 'user_id' => $user1->id]);
        Kanban::create(['title' => 'User 2 Board', 'user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson('/api/kanbans');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['title' => 'User 1 Board'])
            ->assertJsonMissing(['title' => 'User 2 Board']);
    }

    public function test_user_cannot_show_another_users_kanban()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $kanban = Kanban::create(['title' => 'User 2 Board', 'user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/kanbans/{$kanban->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_another_users_kanban()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $kanban = Kanban::create(['title' => 'User 2 Board', 'user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
            ->putJson("/api/kanbans/{$kanban->id}", ['title' => 'Updated title']);

        $response->assertStatus(403);
    }

    public function test_user_cannot_delete_another_users_kanban()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $kanban = Kanban::create(['title' => 'User 2 Board', 'user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
            ->deleteJson("/api/kanbans/{$kanban->id}");

        $response->assertStatus(403);
    }

    public function test_user_cannot_access_another_users_columns()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $kanban = Kanban::create(['title' => 'User 2 Board', 'user_id' => $user2->id]);
        $column = Column::create(['title' => 'User 2 Col', 'kanban_id' => $kanban->id, 'user_id' => $user2->id]);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/kanbans/{$kanban->id}/columns");
        $response->assertStatus(403);

        $response = $this->actingAs($user1, 'sanctum')
            ->putJson("/api/columns/{$column->id}", ['title' => 'Unauthorized Update']);
        $response->assertStatus(403);
    }

    public function test_user_cannot_access_another_users_tasks()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $kanban = Kanban::create(['title' => 'User 2 Board', 'user_id' => $user2->id]);
        $column = Column::create(['title' => 'User 2 Col', 'kanban_id' => $kanban->id, 'user_id' => $user2->id]);
        $task = Task::create(['title' => 'User 2 Task', 'column_id' => $column->id, 'user_id' => $user2->id, 'priority' => 'low']);

        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/columns/{$column->id}/tasks");
        $response->assertStatus(403);

        $response = $this->actingAs($user1, 'sanctum')
            ->putJson("/api/tasks/{$task->id}", ['title' => 'Unauthorized Update', 'priority' => 'low']);
        $response->assertStatus(403);
    }
}
