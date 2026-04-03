<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Column $column)
    {
        if ($column->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $column->tasks()->orderBy('order')->get();
    }

    public function store(Request $request, Column $column)
    {
        if ($column->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task = $column->tasks()->create(array_merge(
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'order' => 'integer',
                'priority' => 'required|in:low,medium,high',
            ]),
            ['user_id' => Auth::id()]
        ));

        return $task;
    }

    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
        if ($task->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->noContent();
    }

    public function reorder(Request $request)
    {
        foreach ($request->tasks as $item) {
            Task::where('id', $item['id'])
                ->where('user_id', Auth::id())
                ->update([
                    'column_id' => $item['column_id'],
                    'order' => $item['order'],
                ]);
        }

        return response()->json(['message' => 'Reordered']);
    }
}
