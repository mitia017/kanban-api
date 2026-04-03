<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, Column $column)
    {
        $user = $request->user();
        $query = $column->tasks();

        if ($user->role !== 'admin') {
            $query->where('assigned_to', $user->id);
        }

        return TaskResource::collection($query->orderBy('order')->with('assignedTo')->get());
    }

    public function store(Request $request, Column $column)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Security: only admin can assign a task during creation
        if ($request->user()->role !== 'admin') {
            unset($data['assigned_to']);
        }

        $task = $column->tasks()->create($data);

        return new TaskResource($task->load('assignedTo'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'priority' => 'required|in:low,medium,high',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        // Security: only admin can reassign a task
        if ($request->user()->role !== 'admin') {
            unset($data['assigned_to']);
        }

        $task->update($data);

        return new TaskResource($task->load('assignedTo'));
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->noContent();
    }

    public function reorder(Request $request)
    {
        foreach ($request->tasks as $item) {
            Task::where('id', $item['id'])->update([
                'column_id' => $item['column_id'],
                'order' => $item['order'],
            ]);
        }

        return response()->json(['message' => 'Reordered']);
    }

    public function assign(Request $request, Task $task)
    {
        $this->authorize('assign', Task::class);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $task->update(['assigned_to' => $validated['assigned_to']]);

        return new TaskResource($task->load('assignedTo'));
    }

    public function myTasks(Request $request)
    {
        $tasks = Task::where('assigned_to', $request->user()->id)
            ->with(['assignedTo', 'column.kanban'])
            ->get();

        return TaskResource::collection($tasks);
    }
}
