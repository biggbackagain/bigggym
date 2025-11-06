<?php

namespace App\Http\Controllers;

use App\Models\MembershipType;
use Illuminate\Http\Request;

class MembershipTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = MembershipType::orderBy('duration_days')->get();
        return view('admin.membership-types.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.membership-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:membership_types',
            'duration_days' => 'required|integer|min:1',
            'price_general' => 'required|numeric|min:0',
            'price_student' => 'required|numeric|min:0|lte:price_general',
        ], [
            'price_student.lte' => 'El precio de estudiante no puede ser mayor al precio general.',
            'name.unique' => 'Ya existe un plan con este nombre.'
        ]);

        MembershipType::create($validated);

        return redirect()->route('admin.memberships.index')->with('success', 'Nuevo plan creado exitosamente.');
    }

    /**
     * Display the specified resource.
     * (No lo usamos, pero lo dejamos por si acaso)
     */
    public function show(MembershipType $membershipType)
    {
        return redirect()->route('admin.memberships.edit', $membershipType);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MembershipType $membershipType)
    {
        return view('admin.membership-types.edit', ['type' => $membershipType]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MembershipType $membershipType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:membership_types,name,' . $membershipType->id,
            'duration_days' => 'required|integer|min:1',
            'price_general' => 'required|numeric|min:0',
            'price_student' => 'required|numeric|min:0|lte:price_general',
        ], [
            'price_student.lte' => 'El precio de estudiante no puede ser mayor al precio general.',
            'name.unique' => 'Ya existe otro plan con este nombre.'
        ]);

        $membershipType->update($validated);

        return redirect()->route('admin.memberships.index')->with('success', 'Tarifa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MembershipType $membershipType)
    {
        // Verificación de seguridad: No borrar si el plan está en uso
        if ($membershipType->subscriptions()->exists()) {
            return redirect()->route('admin.memberships.index')
                ->with('error', "No se puede eliminar '{$membershipType->name}' porque ya hay miembros suscritos a este plan.");
        }

        $membershipType->delete();

        return redirect()->route('admin.memberships.index')->with('success', 'Plan eliminado exitosamente.');
    }
}