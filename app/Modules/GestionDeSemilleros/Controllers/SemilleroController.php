<?php

namespace App\Modules\GestionDeSemilleros\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\GestionDeSemilleros\Models\Program;
use App\Modules\GestionDeSemilleros\Models\Semillero;
use App\Modules\GestionDeSemilleros\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
class SemilleroController extends Controller
{
    public function index(Request $request)
    {
        $query = Semillero::with(['coordinador', 'programa']);

        if ($request->has('query') && $request->query('query') !== null) {
            $busqueda = $request->query('query');

            $query->where(function ($q) use ($busqueda) {
                $q->where('nombre', 'like', "%$busqueda%")
                ->orWhere('id', $busqueda);
            });
        }
        $semilleros = $query->get();
        return view('GestionSemilleros.semilleros', compact('semilleros'));
    }
    public function create()
    {
        $programas = Program::all();
        $profesores = User::where('tipo', 'profesor')->get();
        return view('GestionSemilleros.create', compact('programas', 'profesores'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre'      => 'required|string',
            'descripcion' => 'required|string',
            'programa_id' => 'required|exists:programa,id',
            'profesor_id' => 'required|exists:usuario,id',
        ]);

        Semillero::create([
            'nombre'              => $request->nombre,
            'descripcion'         => $request->descripcion,
            'programa_id'         => $request->programa_id,
            'coordinador_id'      => $request->profesor_id,
            'fecha_creacion'      => now(),
            'fecha_actualizacion' => now(),
        ]);

        return redirect()->route('semilleros.index')->with('success', 'Semillero creado correctamente');
    }
    public function getProfesoresPorPrograma($id)
    {
        $profesores = User::where('tipo', 'profesor')
            ->where('programa_id', $id)
            ->select('id', 'nombre')
            ->get();

        return response()->json($profesores);
    }
    public function edit($id)
    {
        $semillero = Semillero::findOrFail($id);
        $programas = Program::all();
        $profesores = User::where('programa_id', $semillero->programa_id)
                          ->where('tipo', 'profesor')
                          ->get();

        return view('GestionSemilleros.edit', compact('semillero', 'programas', 'profesores'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'      => 'required|string',
            'descripcion' => 'required|string',
            'programa_id' => 'required|exists:programa,id',
            'profesor_id' => 'required|exists:usuario,id',
        ]);

        $semillero = Semillero::findOrFail($id);
        $semillero->update([
            'nombre'              => $request->nombre,
            'descripcion'         => $request->descripcion,
            'programa_id'         => $request->programa_id,
            'coordinador_id'      => $request->profesor_id,
            'fecha_actualizacion' => now(),
        ]);

        return redirect()->route('semilleros.index')->with('success', 'Semillero actualizado correctamente');
    }
    public function delete($id)
    {
        $semillero = Semillero::findOrFail($id);
        $semillero->delete();

        return redirect()->route('semilleros.index')->with('success', 'Semillero eliminado correctamente');
    }
    public function search(Request $request)
    {
        return $this->index($request);
    }
}
