<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\EstadoTarea;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\TareaServicio;
use App\Models\TableroServicio;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;
use App\Models\TareaEstadoHistorial;
use Illuminate\Support\Facades\Auth;
use App\Models\ColumnaTableroServicio;
use App\Http\Requests\StoreTareaRequest;

class TareaServicioController extends Controller
{
    public function index()
    {
        $tareas = TareaServicio::with(['area', 'usuario', 'estado', 'columna'])->get();
        return view('configuracion.servicios.tableros.show', compact('tareas'));
    }

    public function create(Cliente $cliente, Servicio $servicio, TableroServicio $tablero, ColumnaTableroServicio $columna)
    {
        abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
        abort_unless($tablero->servicio_id === $servicio->id, 404);
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        // Áreas contratadas para este servicio (horas_contratadas > 0)
        $areas   = $servicio->areasContratadas()->orderBy('areas.nombre')->get();
        $estados = EstadoTarea::orderBy('nombre')->get();


        return view('configuracion.servicios.tareas.create', compact(
            'areas',
            'estados',
            'cliente',
            'servicio',
            'tablero',
            'columna'
        ));
    }

    public function store(Request $request, Cliente $cliente, Servicio $servicio, TableroServicio $tablero, ColumnaTableroServicio $columna)
    {

        abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
        abort_unless($tablero->servicio_id === $servicio->id, 404);
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        // IDs válidos de áreas
        $areaIdsValidas = $servicio->areaIdsContratadas();

        $validated = $request->validate([
            'titulo'            => ['required', 'string', 'max:255'],
            'estado_id'         => ['required', Rule::exists('estado_tarea', 'id')],
            'area_id'           => ['required', 'integer', Rule::in($areaIdsValidas->all())],
            'usuario_id'        => ['required', Rule::exists('users', 'id')],
            'descripcion'       => ['required', 'string'],
            'tiempo_estimado_h' => ['required', 'numeric', 'min:0'],
            'fecha_de_entrega'  => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        /* */
        // Sanitizar la descripción
        $validated['descripcion'] = Purifier::clean($validated['descripcion'], [
            'HTML.Allowed' => 'p,b,strong,i,em,u,strike,ul,ol,li,a[href],br,span[style],div[style],h1,h2,h3,img[src|alt|width|height],video[src|controls|width|height]',
            'CSS.AllowedProperties' => 'color,background-color,text-align,font-weight,font-style,text-decoration',
            'AutoFormat.AutoParagraph' => true,
            'AutoFormat.RemoveEmpty' => true,
        ]);


        // siguiente posición en la columna
        $nextPos = TareaServicio::where('columna_id', $columna->id)->max('posicion');
        $nextPos = is_null($nextPos) ? 1 : $nextPos + 1;

        $tarea = DB::transaction(function () use ($validated, $columna, $nextPos) {
            $tarea = TareaServicio::create([
                'id'                => (string) Str::uuid(),
                'columna_id'        => $columna->id,
                'estado_id'         => $validated['estado_id'],
                'area_id'           => $validated['area_id'],
                'usuario_id'        => $validated['usuario_id'], // responsable principal
                'titulo'            => $validated['titulo'],
                'descripcion'       => $validated['descripcion'],
                'tiempo_estimado_h' => $validated['tiempo_estimado_h'],
                'fecha_de_entrega'  => $validated['fecha_de_entrega'] ?? null,
                'posicion'          => $nextPos,
                'archivada'         => false,
            ]);

            $userId = Auth::id() ?? $validated['usuario_id'];
            // historial de estado inicial
            TareaEstadoHistorial::create([
                'id'                 => (string) Str::uuid(),
                'tarea_id'           => $tarea->id,
                'cambiado_por'       => $userId,
                'estado_id_anterior' => null,
                'estado_id_nuevo'    => $validated['estado_id'],
                'observacion'        => 'Creación de tarea',
            ]);

            return $tarea;
        });

        // Notificación + Calendar (descomenta si ya configuraste)
        // User::find($v['usuario_id'])?->notify(new NotificacionAsignacionTarea($tarea));
        // dispatch(new CreateTaskCalendarEvent($tarea->id, $v['usuario_id']))->onQueue('calendar');

        return redirect()->route('configuracion.servicios.tableros.show', [
            'cliente' => $cliente->id,
            'servicio' => $servicio->id,
            'tablero' => $tablero->id,
        ])->with('success', "Tarea «{$tarea->titulo}» creada exitosamente.");
    }


    public function show(
        Request $request,
        Cliente $cliente,
        Servicio $servicio,
        TableroServicio $tablero,
        ColumnaTableroServicio $columna,
        TareaServicio $tarea
    ) {
        // Validar cadena jerárquica (anti ID tampering)
        abort_unless(optional($tarea->columna)->id === $columna->id, 404);
        abort_unless(optional($columna->tablero)->id === $tablero->id, 404);
        abort_unless($tablero->servicio_id === $servicio->id, 404);
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        // Eager loading necesario para la vista
        $tarea->load([
            'estado',
            'area',
            'usuario',
            'timeLogs',
            'columna.tablero',
            'columna.tablero.cliente',
        ]);

        return view('configuracion.servicios.tareas.show', compact(
            'cliente',
            'servicio',
            'tablero',
            'columna',
            'tarea'
        ));
    }

    public function usuariosPorArea(Area $area, Request $request)
    {
        $servicioId = $request->query('servicio_id');

        if (!$servicioId) {
            return response()->json(['message' => 'servicio_id requerido'], 400);
        }

        // Valida que el AREA esté contratada para ese SERVICIO (horas_contratadas > 0)
        $contratada = DB::table('mapa_areas')
            ->join('mapa_del_cliente', 'mapa_del_cliente.id', '=', 'mapa_areas.mapa_del_cliente_id')
            ->where('mapa_del_cliente.servicio_id', $servicioId)
            ->where('mapa_areas.area_id', $area->id)
            ->where('mapa_areas.horas_contratadas', '>', 0)
            ->exists();

        if (!$contratada) {
            return response()->json([
                'message' => 'El área no está contratada para este servicio o no tiene horas.'
            ], 404);
        }

        // Devuelve los usuarios de esa área
        $users = $area->usuarios()->select('id', 'name')->orderBy('name')->get();

        return response()->json($users);
    }



    public function horasContratadasArea(Servicio $servicio, Area $area)
    {
        // Seguridad: solo áreas que realmente están mapeadas a este servicio con horas > 0
        $mapaId = optional($servicio->mapa)->id;
        if (!$mapaId) {
            return response()->json(['horas' => 0, 'message' => 'El servicio no tiene mapa configurado'], 200);
        }

        $horas = (float) DB::table('mapa_areas')
            ->where('mapa_del_cliente_id', $mapaId)
            ->where('area_id', $area->id)
            ->sum('horas_contratadas');

        return response()->json(['horas' => $horas]);
    }
}