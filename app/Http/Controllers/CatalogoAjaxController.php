<?php

namespace App\Http\Controllers;

use App\Models\FaseServicio;
use App\Models\TipoServicio;
use Illuminate\Http\Request;

class CatalogoAjaxController extends Controller
{
    public function tiposPorModalidad($modalidadId)
    {
        $tipos = TipoServicio::select('id', 'nombre')
            ->where('modalidad_id', $modalidadId)
            ->orderBy('nombre')
            ->get();

        return response()->json(['tipos' => $tipos]);
    }

    public function fasesPorTipo($tipoId)
    {
        $fases = FaseServicio::select('id', 'nombre', 'descripcion')
            ->where('tipo_servicio_id', $tipoId) // ajusta a tu columna real
            ->orderBy('id')
            ->get();

        return response()->json(['fases' => $fases]);
    }
}