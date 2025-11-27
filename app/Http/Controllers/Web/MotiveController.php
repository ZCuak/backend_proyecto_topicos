<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Motive;
use Illuminate\Http\Request;

class MotiveController extends Controller
{
    /**
     * Mostrar listado de motivos.
     */
    public function index()
    {
        $motives = Motive::orderBy('name')->paginate(10);

        return view('motives.index', compact('motives'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        return view('motives.create');
    }

    /**
     * Guardar un nuevo motivo.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:motives,name',
        ]);

        Motive::create($data);

        return redirect()->route('motives.index')->with('success', 'Motivo creado correctamente.');
    }

    /**
     * Mostrar formulario de edición.
     */
    public function edit(Motive $motive)
    {
        return view('motives.edit', compact('motive'));
    }

    /**
     * Actualizar motivo.
     */
    public function update(Request $request, Motive $motive)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:motives,name,' . $motive->id,
        ]);

        $motive->update($data);

        return redirect()->route('motives.index')->with('success', 'Motivo actualizado correctamente.');
    }

    /**
     * Eliminar motivo.
     */
    public function destroy(Motive $motive)
    {
        $motive->delete();

        return redirect()->route('motives.index')->with('success', 'Motivo eliminado.');
    }
}
