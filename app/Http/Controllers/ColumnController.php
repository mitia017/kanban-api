<?php

namespace App\Http\Controllers;

use App\Models\Column;
use App\Models\Kanban;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function index(Kanban $kanban)
    {
        return $kanban->columns()->with('tasks')->get();
    }

    public function store(Request $request, Kanban $kanban)
    {
        $column = $kanban->columns()->create($request->validate([
            'title' => 'required|string|max:255',
            'order' => 'integer',
        ]));

        return $column;
    }

    public function update(Request $request, Column $column)
    {
        $column->update($request->validate([
            'title' => 'string|max:255',
            'order' => 'integer',
        ]));

        return $column;
    }

    public function destroy(Column $column)
    {
        $column->delete();

        return response()->noContent();
    }

    public function reorder(Request $request)
    {
        foreach ($request->columns as $item) {
            Column::where('id', $item['id'])->update([
                'order' => $item['order'],
            ]);
        }

        return response()->json(['message' => 'Reordered']);
    }
}
