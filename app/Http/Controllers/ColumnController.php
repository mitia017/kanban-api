<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Kanban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ColumnController extends Controller
{
    public function index(Kanban $kanban)
    {
        if ($kanban->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $kanban->columns()->with('tasks')->get();
    }

    public function store(Request $request, Kanban $kanban)
    {
        if ($kanban->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $column = $kanban->columns()->create(array_merge(
            $request->validate([
                'title' => 'required|string|max:255',
                'order' => 'integer',
            ]),
            ['user_id' => Auth::id()]
        ));

        return $column;
    }

    public function update(Request $request, Column $column)
    {
        if ($column->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $column->update($request->validate([
            'title' => 'string|max:255',
            'order' => 'integer',
        ]));

        return $column;
    }

    public function destroy(Column $column)
    {
        if ($column->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $column->delete();

        return response()->noContent();
    }

    public function reorder(Request $request)
    {
        foreach ($request->columns as $item) {
            Column::where('id', $item['id'])
                ->where('user_id', Auth::id())
                ->update([
                    'order' => $item['order'],
                ]);
        }

        return response()->json(['message' => 'Reordered']);
    }
}
