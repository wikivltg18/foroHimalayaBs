<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cliente;
use App\Models\TipoContrato;
use App\Models\EstadoCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    /**
     * Muestra la lista de clientes con filtro por nombre.
     */
    public function index(Request $request)
    {
        if (!Auth::user()->can('consultar clientes')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Obtener parámetros de búsqueda y paginación
        $buscar   = $request->input('buscar');
        $estado   = $request->input('estado');
        $cantidad = (int) $request->input('cantidad', 5);
        $cantidad = $cantidad > 0 && $cantidad <= 100 ? $cantidad : 5;

        // Consulta con filtros y relaciones
        $clientes = Cliente::with(['usuario', 'estado', 'tiposContrato'])
            ->when($buscar, fn($query) => $query->where('nombre', 'like', "%{$buscar}%"))
            ->when($estado, fn($query) => $query->whereHas('estado', fn($q) => $q->where('nombre', $estado)))
            ->orderBy('nombre')
            ->paginate($cantidad)
            ->appends($request->query());

        // Pasar los datos a la vista
        return view('clientes.index', compact('clientes', 'buscar', 'estado', 'cantidad'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create()
    {
        if (!Auth::user()->can('registrar cliente')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Cargar usuarios con cargo "Director Ejecutivo"
        $usuarios = User::where('id_cargo', 6)
            ->orWhereHas('cargo', function ($query) {
                $query->where('nombre', 'Director Ejecutivo');
            })
            ->get();

        // Obtener todos los tipos de contratos y estados de clientes
        $tiposDeContratos = TipoContrato::all();
        $estadosClientes  = EstadoCliente::all();

        // Pasar los datos a la vista
        return view('clientes.create', compact('usuarios', 'estadosClientes', 'tiposDeContratos'));
    }

    /**
     * Guarda un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('registrar cliente')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        
        // Validación de los datos
        $request->validate([
            'logo'               => 'nullable|image|max:2048', // 2MB
            'nombre'             => 'required|string|max:255',
            'correo_electronico' => 'required|email|max:255',
            'telefono'           => 'required|string|max:50',
            'sitio_web'          => 'nullable|url|max:255',
            'usuario_id'         => 'required|exists:users,id',
            'estadoCliente_id'   => 'required|exists:estado_clientes,id',
            'tiposDeContratos'   => 'required|array',
            'tiposDeContratos.*' => 'exists:tipo_contratos,id',
            'url_instagram'      => 'nullable|url|max:255',
            'url_facebook'       => 'nullable|url|max:255',
            'url_youtube'        => 'nullable|url|max:255',
        ]);
        
        try {
        // Guardar dentro de una transacción
        DB::transaction(function () use ($request) {
            // Procesar logo si existe
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos_clientes', 'public');
            }

            // Crear cliente
            $cliente = Cliente::create([
                'logo'                => $logoPath,
                'nombre'              => $request->nombre,
                'correo_electronico'  => $request->correo_electronico,
                'telefono'            => $request->telefono,
                'sitio_web'           => $request->sitio_web,
                'id_usuario'          => $request->usuario_id,
                'id_estado_cliente'   => $request->estadoCliente_id,
            ]);

            // Asociar contratos (tabla pivote)
            $cliente->tiposContrato()->sync($request->tiposDeContratos);

            // Registrar redes sociales (crear/actualizar/eliminar)
            $redes = collect([
                ['nombre_rsocial' => 'Instagram', 'url_rsocial' => $request->url_instagram],
                ['nombre_rsocial' => 'Facebook',  'url_rsocial' => $request->url_facebook],
                ['nombre_rsocial' => 'YouTube',   'url_rsocial' => $request->url_youtube],
            ]);

            $redes->each(function ($red) use ($cliente) {
                $query = $cliente->redSocial()->where('nombre_rsocial', $red['nombre_rsocial']);
                if (filled($red['url_rsocial'])) {
                    $exists = $query->first();
                    if ($exists) {
                        $exists->update(['url_rsocial' => $red['url_rsocial']]);
                    } else {
                        $cliente->redSocial()->create($red);
                    }
                } else {
                    // Si viene vacío, elimina la red existente (si la hay)
                    $query->delete();
                }
            });
        });

        return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente.');
            } catch (\Exception $e) {
                // Manejo de errores y redirección con mensaje de error
                return redirect()->route('clientes.index')->with('error', 'Hubo un problema al crear el cliente.');

            }
    }

    /**
     * Muestra el formulario para editar un cliente.
     */
    public function edit(Cliente $cliente)
    {
        if (!Auth::user()->can('modificar cliente')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Cargar usuarios con cargo "Director Ejecutivo"
        $usuarios = User::where('id_cargo', 6)
            ->orWhereHas('cargo', function ($query) {
                $query->where('nombre', 'Director Ejecutivo');
            })
            ->get();

        // Obtener todos los tipos de contratos y estados de clientes
        $tiposDeContratos = TipoContrato::all();
        $estadosClientes  = EstadoCliente::all();

        // Cargar relaciones del cliente para el formulario de edición
        return view('clientes.edit', compact('cliente', 'usuarios', 'estadosClientes', 'tiposDeContratos'));
    }

    /**
     * Actualiza la información del cliente.
     */
    public function update(Request $request, Cliente $cliente)
    {
        if (!Auth::user()->can('modificar cliente')) {
            return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
        }
        // Validación de los datos recibidos del formulario de edición del cliente
        $request->validate([
            'logo'               => 'nullable|image|max:2048',
            'nombre'             => 'required|string|max:255',
            'correo_electronico' => 'required|email|max:255',
            'telefono'           => 'required|string|max:50',
            'sitio_web'          => 'nullable|url|max:255',
            'usuario_id'         => 'required|exists:users,id',
            'estadoCliente_id'   => 'required|exists:estado_clientes,id',
            'tiposDeContratos'   => 'required|array',
            'tiposDeContratos.*' => 'exists:tipo_contratos,id',
            'url_instagram'      => 'nullable|url|max:255',
            'url_facebook'       => 'nullable|url|max:255',
            'url_youtube'        => 'nullable|url|max:255',
        ]);

        try {
        // Iniciar transacción para guardar los cambios
        DB::transaction(function () use ($request, $cliente) {
            // Procesar logo si existe y actualizarlo
            $logoPath = $cliente->logo;
            if ($request->hasFile('logo')) {
                if ($logoPath) {
                    Storage::disk('public')->delete($logoPath);
                }
                $logoPath = $request->file('logo')->store('logos_clientes', 'public');
            }

            // Actualizar cliente con los nuevos datos
            $cliente->update([
                'logo'               => $logoPath,
                'nombre'             => $request->nombre,
                'correo_electronico' => $request->correo_electronico,
                'telefono'           => $request->telefono,
                'sitio_web'          => $request->sitio_web,
                'id_usuario'         => $request->usuario_id,
                'id_estado_cliente'  => $request->estadoCliente_id,
            ]);

            // Actualizar contratos (tabla pivote)
            $cliente->tiposContrato()->sync($request->tiposDeContratos);

            // Registrar redes sociales (actualizar/crear/eliminar)
            $redes = collect([
                ['nombre_rsocial' => 'Instagram', 'url_rsocial' => $request->url_instagram],
                ['nombre_rsocial' => 'Facebook',  'url_rsocial' => $request->url_facebook],
                ['nombre_rsocial' => 'YouTube',   'url_rsocial' => $request->url_youtube],
            ]);

            $redes->each(function ($red) use ($cliente) {
                $query = $cliente->redSocial()->where('nombre_rsocial', $red['nombre_rsocial']);
                if (filled($red['url_rsocial'])) {
                    $exists = $query->first();
                    if ($exists) {
                        $exists->update(['url_rsocial' => $red['url_rsocial']]);
                    } else {
                        $cliente->redSocial()->create($red);
                    }
                } else {
                    // Si el campo viene vacío, elimina la existente
                    $query->delete();
                }
            });
        });

        // Redirecciona a la pagina inicial de cliente con mensaje de éxito
        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
            } catch (\Exception $e) {
                // Manejo de errores y redirección con mensaje de error
                return redirect()->route('clientes.index')->with('error', 'Hubo un problema al actualizar el cliente.');

            }
    }

    /**
     * Elimina un cliente de la base de datos.
     */
    public function destroy(Cliente $cliente)
{
    if (!Auth::user()->can('eliminar cliente')) {
        return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
    }
    try {
        DB::transaction(function () use ($cliente) {
            // Eliminar logo físico si existe
            if ($cliente->logo) {
                Storage::disk('public')->delete($cliente->logo);
            }

            // Limpiar relaciones
            $cliente->tiposContrato()->detach();
            $cliente->redSocial()->delete();

            // Eliminar cliente
            $cliente->delete();
        });

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    } catch (\Exception $e) {
        Log::error('Error al eliminar cliente: ' . $e->getMessage());

        return redirect()->route('clientes.index')->with('error', 'Hubo un problema al eliminar el cliente.');
    }
}
}