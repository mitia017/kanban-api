<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Column $column)
    {
        return $column->tasks()->orderBy('order')->get();
    }

    public function store(Request $request, Column $column)
    {
        $task = $column->tasks()->create($request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'priority' => 'required|in:low,medium,high',
        ]));

        return $task;
    }

    public function update(Request $request, Task $task)
    {
        $task->update($request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer',
            'priority' => 'required|in:low,medium,high',
        ]));

        return $task;
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return response()->noContent();
    }
}
