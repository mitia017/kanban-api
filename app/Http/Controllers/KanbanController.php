<?php

namespace App\Http\Controllers;

use App\Models\Kanban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    public function index()
    {
        return Auth::user()->kanbans()->with('columns.tasks')->get();
    }

    public function store(Request $request)
    {
        $kanban = Auth::user()->kanbans()->create($request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]));

        return $kanban;
    }

    public function show(Kanban $kanban)
    {
        if ($kanban->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $kanban->load('columns.tasks');
    }

    public function update(Request $request, Kanban $kanban)
    {
        if ($kanban->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kanban->update($request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
        ]));

        return $kanban;
    }

    public function destroy(Kanban $kanban)
    {
        if ($kanban->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $kanban->delete();

        return response()->noContent();
    }
}
