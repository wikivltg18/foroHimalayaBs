<?php

namespace App\Http\Controllers;

use App\Models\Modalidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModalidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Evaluar si el usuario tiene permiso para consultar modalidad de servicio
        if (!Auth::user()->can('consultar modalidad')) {
            return response()->json(['redirect' => route('dashboard'),'error' => 'No tienes acceso a este módulo.']);
        }
        // Obtener todas las modalidades
        $modalidades = Modalidad::all();
        
        // Retornar las modalidades en formato JSON
        return response()->json($modalidades);
    }
}