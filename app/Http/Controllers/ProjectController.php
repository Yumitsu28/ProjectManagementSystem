<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $projects = $request->user()->projects()->get();
        return response()->json($projects);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Planning,In Progress,On Hold,Completed',
            'priority' => 'required|in:Low,Medium,High',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
        ]);

        $project = $request->user()->projects()->create($validated);
        return response()->json($project, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        $project = Project::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        return response()->json($project);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $project = Project::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();

        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'project_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:Planning,In Progress,On Hold,Completed',
            'priority' => 'required|in:Low,Medium,High',
            'start_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:start_date',
        ]);

        $project->update($validated);
        return response()->json($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $project = Project::where('id', $id)->where('user_id', $request->user()->id)->firstOrFail();
        $project->delete();
        return response()->json(null, 204);
    }
}

