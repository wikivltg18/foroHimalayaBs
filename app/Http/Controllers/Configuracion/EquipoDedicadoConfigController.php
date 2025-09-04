<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller; // <-- IMPORTANTE
use App\Models\Cliente;
use Illuminate\Http\Request;

class EquipoDedicadoConfigController extends Controller
{
    public function index(Cliente $cliente)
    {
        return view('configuracion.equipodedicado.index', compact('cliente'));
    }

    public function create(Cliente $cliente)
    {
        return view('configuracion.equipodedicado.create', compact('cliente'));
    }

    public function store(Request $request, Cliente $cliente)
    {
        // ...
    }
}