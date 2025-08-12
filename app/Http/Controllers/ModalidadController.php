<?php

namespace App\Http\Controllers;

use App\Models\Modalidad;
use Illuminate\Http\Request;

class ModalidadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener todas las modalidades
        $modalidades = Modalidad::all();
        
        // Retornar las modalidades en formato JSON
        return response()->json($modalidades);
    }
}