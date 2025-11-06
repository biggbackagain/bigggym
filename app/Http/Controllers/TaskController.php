<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate(['title' => 'required|string|max:255']);
        Task::create($request->only('title'));
        return redirect()->route('dashboard')->with('success', 'Tarea creada.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $task->update(['is_completed' => $request->has('is_completed')]);
        return redirect()->route('dashboard')->with('success', 'Tarea actualizada.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('dashboard')->with('success', 'Tarea eliminada.');
    }

    // Ocultamos las vistas que no usaremos
    public function index() { return redirect()->route('dashboard'); }
    public function create() { return redirect()->route('dashboard'); }
    public function show(Task $task) { return redirect()->route('dashboard'); }
    public function edit(Task $task) { return redirect()->route('dashboard'); }
}