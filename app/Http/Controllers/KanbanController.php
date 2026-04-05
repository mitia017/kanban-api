<?php

namespace App\Http\Controllers;

use App\Models\Kanban;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    public function index()
    {
        return Kanban::with('columns.tasks')->get();
    }

    public function store(Request $request)
    {
        $kanban = Kanban::create($request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        return $kanban;
    }

    public function show(Kanban $kanban)
    {
        return $kanban->load('columns.tasks');
    }

    public function update(Request $request, Kanban $kanban)
    {
        $kanban->update($request->validate([
            'title' => 'string|max:255', 'description' => 'nullable|string',
        ]));

        return $kanban;
    }

    public function destroy(Kanban $kanban)
    {
        $kanban->delete();

        return response()->noContent();
    }
}
