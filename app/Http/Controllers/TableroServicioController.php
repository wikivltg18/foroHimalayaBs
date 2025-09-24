<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\EstadoTarea;
use Illuminate\Http\Request;
use App\Models\TableroServicio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\EstadoTableroServicio;
use App\Models\ColumnaTableroServicio;
use App\Models\FaseDeServicioInstancia;

class TableroServicioController extends Controller
{
    /**
     * Listado de tableros del cliente
     */
    public function index(Cliente $cliente)
    {
        // Cargamos columnas y servicio con tipo/modalidad para mostrar metadatos
        $tableros = TableroServicio::where('cliente_id', $cliente->id)
            ->with([
                'estado',
                'columnas',
                'servicio.tipo',
                'servicio.modalidad',
            ])
            ->orderByDesc('created_at')
            ->paginate(5);

        return view('configuracion.servicios.tableros.index', compact('cliente', 'tableros'));
    }

public function show(Cliente $cliente, Servicio $servicio, TableroServicio $tablero)
    {
        $tablero->load([
            'estado',
            'columnas' => fn($q) => $q->orderBy('posicion')->with(['tareas.estado']),
            'servicio.tipo',
            'servicio.modalidad',
        ]);

        $finalIds = EstadoTarea::finalIds(); // <-- ya funciona

        $tablero->loadCount([
            'tareas',
            'tareas as pendientes_count' => fn($q) => $q->where(function ($qq) use ($finalIds) {
                $qq->whereNull('estado_id')->orWhereNotIn('estado_id', $finalIds);
            }),
            'tareas as completas_count'  => fn($q) => $q->whereIn('estado_id', $finalIds),
        ]);

        return view('configuracion.servicios.tableros.show', compact('cliente', 'servicio', 'tablero'));
    }

    /**
     * Formulario de creación
     */
    public function create(Cliente $cliente, Servicio $servicio)
    {
        $estados = EstadoTableroServicio::orderBy('nombre')->get();

        // Fases específicas del servicio (serán columnas)
        $fasesInstancias = FaseDeServicioInstancia::where('servicio_id', $servicio->id)
            ->orderBy('posicion')
            ->with('plantilla')
            ->get();

        return view(
            'configuracion.servicios.tableros.create',
            compact('estados', 'cliente', 'servicio', 'fasesInstancias')
        );
    }

    /**
     * Guarda el tablero + columnas (fases)
     */
    public function store(Request $request, Cliente $cliente, Servicio $servicio)
    {


        // Validamos SOLO lo que viene del form; cliente/servicio se toman de la ruta
        $data = $request->validate([
            'nombre_del_tablero'       => ['required', 'string', 'max:150'],
            'estado_tablero_id'        => ['required', 'exists:estado_tablero_servicios,id'],

            'nombre_del_servicio'      => ['nullable', 'string', 'max:80'],
            'nombre_cliente'           => ['nullable', 'string', 'max:150'],
            'nombre_modalidad'         => ['nullable', 'string', 'max:50'],
            'nombre_tipo_de_servicio'  => ['nullable', 'string', 'max:150'],

            // columnas basadas en fases
            'columnas'                 => ['required', 'array', 'min:1'],
            'columnas.*.nombre'        => ['required', 'string', 'max:150'],
            'columnas.*.descripcion'   => ['nullable', 'string'],
            'columnas.*.orden'         => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($data, $cliente, $servicio) {
                $tablero = TableroServicio::create([
                    'nombre_del_tablero'      => $data['nombre_del_tablero'],
                    'servicio_id'             => $servicio->id,        // desde la ruta
                    'cliente_id'              => $cliente->id,         // desde la ruta
                    'estado_tablero_id'       => $data['estado_tablero_id'],

                    // Si no vienen en el form, intentamos poblar con relaciones
                    'nombre_del_servicio'     => $data['nombre_del_servicio']
                        ?? ($servicio->nombre_servicio ?? $servicio->nombre_del_servicio),
                    'nombre_cliente'          => $data['nombre_cliente'] ?? $cliente->nombre,
                    'nombre_modalidad'        => $data['nombre_modalidad'] ?? optional($servicio->modalidad)->nombre,
                    'nombre_tipo_de_servicio' => $data['nombre_tipo_de_servicio'] ?? optional($servicio->tipo_servicio)->nombre,
                ]);

                foreach ($data['columnas'] as $col) {
                    ColumnaTableroServicio::create([
                        'tablero_servicio_id'  => $tablero->id,
                        'nombre_de_la_columna' => $col['nombre'],
                        'descripcion'          => $col['descripcion'] ?? null,
                        'posicion'             => $col['orden'],
                    ]);
                }
            });

            return redirect()
                ->route('configuracion.servicios.tableros.index', ['cliente' => $cliente->id])
                ->with('success', 'Tablero creado correctamente.');
        } catch (\Throwable $e) {
            Log::error('[Tableros] Error al crear tablero', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Ocurrió un error guardando el tablero. Intenta nuevamente.']);
        }
    }

    /**
     * Formulario de edición
     */
    public function edit(Cliente $cliente, Servicio $servicio, TableroServicio $tablero)
    {
        $tablero->load(['estado', 'columnas']);
        $estados = EstadoTableroServicio::orderBy('nombre')->get();

        return view('configuracion.servicios.tableros.edit', compact('cliente', 'servicio', 'tablero', 'estados'));
    }

    /**
     * Actualiza el tablero
     */
    public function update(Request $request, Cliente $cliente, Servicio $servicio, TableroServicio $tablero)
    {
        $data = $request->validate([
            'nombre_del_tablero'       => ['required', 'string', 'max:150'],
            'estado_tablero_id'        => ['required', 'exists:estado_tablero_servicios,id'],
        ]);

        $tablero->update($data);

        return redirect()
            ->route('configuracion.servicios.tableros.index', ['cliente' => $cliente->id])
            ->with('success', 'Tablero actualizado correctamente.');
    }

    /**
     * Elimina (soft delete)
     */
    public function destroy(Cliente $cliente, Servicio $servicio, TableroServicio $tablero)
    {
        $tablero->delete();

        return redirect()
            ->route('configuracion.servicios.tableros.index', ['cliente' => $cliente->id])
            ->with('success', 'Tablero eliminado correctamente.');
    }

    public function listaTableros(Request $request)
    {
        $q        = trim($request->get('q', ''));
        $perPage  = (int) $request->get('per_page', 5);
        $sort     = $request->get('sort', 'created_at');   // cliente | servicio | modalidad | tipo | estado | created_at
        $dir      = $request->get('dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $query = TableroServicio::with([
            'estado',
            'cliente',
            'servicio.modalidad',
            // soporta ambas: Servicio::tipo() o Servicio::tipo_servicio()
            'servicio.tipo',

        ]);

        // Búsqueda simple por cliente, servicio o nombre de tablero
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('nombre_cliente', 'like', "%{$q}%")
                    ->orWhere('nombre_del_servicio', 'like', "%{$q}%")
                    ->orWhere('nombre_del_tablero', 'like', "%{$q}%")
                    ->orWhereHas('cliente', fn($c) => $c->where('nombre', 'like', "%{$q}%"))
                    ->orWhereHas('servicio', fn($s) => $s->where('nombre_servicio', 'like', "%{$q}%"));
            });
        }

        // Ordenamiento
        switch ($sort) {
            case 'cliente':
                $query->orderBy('nombre_cliente', $dir);
                break;
            case 'servicio':
                $query->orderBy('nombre_del_servicio', $dir);
                break;
            case 'modalidad':
                // si no está cacheado en el tablero, ordena por created_at (simple)
                $query->orderBy('nombre_modalidad', $dir)->orderBy('created_at', 'desc');
                break;
            case 'tipo':
                $query->orderBy('nombre_tipo_de_servicio', $dir)->orderBy('created_at', 'desc');
                break;
            case 'estado':
                $query->orderBy('estado_tablero_id', $dir)->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('created_at', $dir);
        }

        $tableros = $query->paginate($perPage)->appends($request->query());

        return view('configuracion.servicios.tableros.listaTableros', compact('tableros', 'q', 'perPage', 'sort', 'dir'));
    }
}