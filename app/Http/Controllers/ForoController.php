<?php

namespace App\Http\Controllers;
use App\Models\TareaServicio;
use Illuminate\Http\Request;

class ForoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = TareaServicio::query()
            ->with(['columna.tablero.cliente', 'estado', 'area', 'usuario'])
            ->where('area_id', $user->id_area)
            ->where('usuario_id', $user->id);

        // Filtros de búsqueda
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'LIKE', "%{$search}%")
                  ->orWhereHas('columna.tablero.cliente', function ($cq) use ($search) {
                      $cq->where('nombre', 'LIKE', "%{$search}%");
                  });
            });
        }

        $tareas = $query->latest()->paginate(10);

        return view('foro.index', compact('tareas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
