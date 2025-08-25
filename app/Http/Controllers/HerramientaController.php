<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HerramientaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Evaluar si el usuario tiene permiso para consultar fases de servicio
        if (!Auth::user()->can('gestiòn tipos y fases de servicio')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }

        return view('herramientas.index');
    }

}