<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TableroServicio;

class EstadoTableroServicioController extends Controller
{
    public function update(Request $request, TableroServicio $tablero)
    {
        $data = $request->validate([
            'estado' => ['required', 'in:Activo,Terminado'],
        ]);

        try {
            if ($data['estado'] === 'Terminado') {
                $tablero->markAsTerminated();  // lanza DomainException si hay pendientes
                return back()->with('success', 'Tablero finalizado correctamente.');
            }

            if ($data['estado'] === 'Activo') {
                $tablero->markAsActive();
                return back()->with('success', 'Tablero reactivado.');
            }

            return back()->with('info', 'Sin cambios.');
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo actualizar el estado del tablero.');
        }
    }
}