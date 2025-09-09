<?php

namespace App\Http\Controllers\Configuracion;

use App\Models\Area;
use App\Models\Cliente;
use App\Models\Modalidad;
use App\Models\FaseServicio;
use App\Models\TipoServicio;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Models\FaseDeServicioInstancia;
use App\Http\Controllers\Controller;          // <-- Importante
use App\Models\Servicio;                      // <-- para edit/update/destroy

class ServiciosConfigController extends Controller
{
    public function index(Cliente $cliente)
    {

        $servicios = Servicio::query()
            ->with([
                'modalidad:id,nombre',
                'tipo:id,nombre',
                'mapa.mapaAreas.area:id,nombre',
                'fases' // ya ordenadas por ->orderBy('posicion') en el modelo
            ])
            ->where('cliente_id', $cliente->id)
            ->orderByDesc('id')
            ->get();

        return view('configuracion.servicios.index', compact('cliente', 'servicios'));
    }

    public function create(Request $request, Cliente $cliente)
    {
        // Catálogos base
        $modalidades = Modalidad::select('id', 'nombre')->orderBy('nombre')->get();

        // Modalidad y tipo "iniciales" para el primer render
        $selectedModalidadId = (string)($request->query('modalidad_id', $modalidades->first()->id ?? ''));
        $tipos = TipoServicio::select('id', 'nombre')
            ->when($selectedModalidadId, fn($q) => $q->where('modalidad_id', $selectedModalidadId))
            ->orderBy('nombre')
            ->get();

        $selectedTipoId = (string)($request->query('tipo_servicio_id', $tipos->first()->id ?? ''));

        // Mapa de áreas
        $areasCatalog = Area::select('id', 'nombre')->orderBy('nombre')->get();

        return view('configuracion.servicios.create', [
            'cliente'             => $cliente,
            'modalidades'         => $modalidades,
            'tipos'               => $tipos,
            'areasCatalog'        => $areasCatalog,
            'selectedModalidadId' => $selectedModalidadId,
            'selectedTipoId'      => $selectedTipoId,
        ]);
    }

    public function store(Request $request, Cliente $cliente)
    {
        // Validar los datos
        $data = $request->validate([
            'nombre_servicio'  => ['required', 'string', 'max:150'],
            'modalidad_id'     => ['required', Rule::exists('modalidads', 'id')],
            'tipo_servicio_id' => [
                'required',
                Rule::exists('tipo_servicios', 'id')
                    ->where(fn($q) => $q->where('modalidad_id', $request->input('modalidad_id')))
            ],
            'mapa'             => ['sometimes', 'array'],
            'mapa.*'           => ['nullable', 'numeric', 'min:0'],
            'fases'            => ['required', 'json']
        ]);

        DB::transaction(function () use ($request, $cliente, $data) {
            // Crear el servicio
            $servicio = Servicio::create([
                'cliente_id'       => $cliente->id,
                'nombre_servicio'  => $data['nombre_servicio'],
                'modalidad_id'     => $data['modalidad_id'],
                'tipo_servicio_id' => $data['tipo_servicio_id'],
            ]);

            // Crear/actualizar mapa de horas si viene
            if (array_key_exists('mapa', $data)) {
                $mapa = $servicio->mapa()->firstOrCreate([]);
                foreach ($data['mapa'] as $areaId => $horas) {
                    $horas = is_null($horas) ? 0 : $horas;
                    $mapa->mapaAreas()->updateOrCreate(
                        ['area_id' => $areaId],
                        ['horas_contratadas' => $horas]
                    );
                }
            }

            // Procesar las fases en el orden recibido
            $fasesData = json_decode($data['fases'], true);
            foreach ($fasesData as $fase) {
                // Asegurarse de que fase_servicio_id sea null o un entero válido
                $fase_servicio_id = isset($fase['fase_servicio_id']) &&
                    is_numeric($fase['fase_servicio_id']) ?
                    (int)$fase['fase_servicio_id'] : null;

                FaseDeServicioInstancia::create([
                    'servicio_id'      => $servicio->id,
                    'fase_servicio_id' => $fase_servicio_id,
                    'nombre'           => $fase['nombre'],
                    'descripcion'      => $fase['descripcion'] ?? null,
                    'posicion'         => $fase['posicion']
                ]);
            }
        });

        return redirect()
            ->route('config.servicios.index', $cliente->id)
            ->with('success', 'Servicio creado correctamente.');
    }

    /* =======================
     * AJAX
     * ======================= */

    // Tipos por modalidad (id/nombre)
    public function ajaxTiposPorModalidad($modalidadId)
    {
        $tipos = TipoServicio::select('id', 'nombre')
            ->where('modalidad_id', $modalidadId)
            ->orderBy('nombre')
            ->get();

        return response()->json(['tipos' => $tipos]);
    }

    // Fases por tipo (tabla fase_servicios)
    public function ajaxFasesPorTipo($tipoId)
    {
        $fases = FaseServicio::select('id', 'nombre', 'descripcion')
            ->where('tipo_servicio_id', $tipoId)
            ->orderBy('id')
            ->get();

        return response()->json(['fases' => $fases]);
    }

    public function edit(Cliente $cliente, Servicio $servicio)
    {
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        // Catálogos base
        $modalidades = Modalidad::select('id', 'nombre')->orderBy('nombre')->get();

        // Selecciones iniciales (lo del servicio)
        $modalidadDelServicioId = (string) ($servicio->modalidad_id);
        $tipos = TipoServicio::select('id', 'nombre')
            ->when($modalidadDelServicioId, fn($q) => $q->where('modalidad_id', $modalidadDelServicioId))
            ->orderBy('nombre')
            ->get();

        $selectedTipoId = (string) ($servicio->tipo_servicio_id);

        // Tipo actual + sus fases (plantilla)
        $tipoActual = TipoServicio::select('id', 'nombre')->find($servicio->tipo_servicio_id);
        $fasesTipoActual = collect();
        if ($servicio->tipo_servicio_id) {
            $fasesTipoActual = FaseServicio::select('id', 'nombre', 'descripcion')
                ->where('tipo_servicio_id', $servicio->tipo_servicio_id)
                ->orderBy('id')
                ->get();
        }

        // Mapa de áreas (si lo usas)
        $areasCatalog = Area::select('id', 'nombre')->orderBy('nombre')->get();

        // Cargar relaciones útiles del servicio (mapa y fases)
        $servicio->load(['mapa.mapaAreas.area:id,nombre', 'fases' => fn($q) => $q->orderBy('posicion')]);

        return view('configuracion.servicios.edit', [
            'cliente'             => $cliente,
            'servicio'            => $servicio,
            'modalidades'         => $modalidades,
            'tipos'               => $tipos,
            'areasCatalog'        => $areasCatalog,
            'modalidadDelServicioId' => $modalidadDelServicioId,
            'selectedTipoId'      => $selectedTipoId,
            'tipoActual'          => $tipoActual,
            'fasesTipoActual'     => $fasesTipoActual,
        ]);
    }

    public function update(Request $request, Cliente $cliente, Servicio $servicio)
    {
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        $data = $request->validate([
            'nombre_servicio'   => ['required', 'string', 'max:150'],
            'modalidad_id'      => ['required', Rule::exists('modalidads', 'id')],
            'tipo_servicio_id'  => [
                'required',
                Rule::exists('tipo_servicios', 'id')
                    ->where(fn($q) => $q->where('modalidad_id', $request->input('modalidad_id')))
            ],
            'mapa'              => ['sometimes', 'array'],
            'mapa.*'            => ['nullable', 'numeric', 'min:0'],
            'fases'             => ['required', 'json'],
        ]);

        DB::transaction(function () use ($servicio, $data) {
            // Actualizar servicio
            $servicio->update([
                'nombre_servicio'  => $data['nombre_servicio'],
                'modalidad_id'     => $data['modalidad_id'],
                'tipo_servicio_id' => $data['tipo_servicio_id'],
            ]);

            // Mapa de horas (opcional)
            if (array_key_exists('mapa', $data)) {
                $mapa = $servicio->mapa()->firstOrCreate([]);
                foreach ($data['mapa'] as $areaId => $horas) {
                    $horas = is_null($horas) ? 0 : $horas;
                    $mapa->mapaAreas()->updateOrCreate(
                        ['area_id' => $areaId],
                        ['horas_contratadas' => $horas]
                    );
                }
            }

            // Procesar las fases en el orden recibido
            $fasesData = json_decode($data['fases'], true);

            // Eliminar las fases existentes
            $servicio->fases()->delete();

            // Insertar las nuevas fases con sus posiciones
            foreach ($fasesData as $fase) {
                // Asegurarse de que fase_servicio_id sea null o un entero válido
                $fase_servicio_id = isset($fase['fase_servicio_id']) &&
                    is_numeric($fase['fase_servicio_id']) ?
                    (int)$fase['fase_servicio_id'] : null;

                FaseDeServicioInstancia::create([
                    'servicio_id'      => $servicio->id,
                    'fase_servicio_id' => $fase_servicio_id,
                    'nombre'           => $fase['nombre'],
                    'descripcion'      => $fase['descripcion'] ?? null,
                    'posicion'         => $fase['posicion']
                ]);
            }
        });

        // Si se envía por AJAX el guardado, podemos responder JSON:
        if ($request->wantsJson()) {
            return response()->json([
                'redirect' => route('config.servicios.index', $cliente->id),
                'message'  => 'Servicio actualizado correctamente.',
            ]);
        }

        return redirect()
            ->route('config.servicios.index', $cliente->id)
            ->with('success', 'Servicio actualizado correctamente.');
    }




    public function destroy(Cliente $cliente, Servicio $servicio)
    {
        abort_unless($servicio->cliente_id === $cliente->id, 404);

        // Eliminar el servicio de manera "soft delete"
        $servicio->delete();

        return redirect()
            ->route('config.servicios.index', $cliente->id)
            ->with('success', 'Servicio eliminado correctamente.');
    }
}