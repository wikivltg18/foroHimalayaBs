<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cliente;
use App\Models\TipoContrato;
use Illuminate\Http\Request;
use App\Models\EstadoCliente;
use Illuminate\Support\Facades\DB;
use App\Models\RedSocial;

class ClienteController extends Controller
{
    /**
     * Muestra la lista de clientes con filtro por nombre.
     */
public function index(Request $request)
{
    $buscar = $request->input('buscar');
    $estado = $request->input('estado');
    $cantidad = $request->input('cantidad', 5); // 5 por defecto


    $clientes = Cliente::with(['usuario', 'estado', 'tiposContrato'])
        ->when($buscar, fn($query) => $query->where('nombre', 'like', "%{$buscar}%"))
        ->when($estado, fn($query) => $query->whereHas('estado', fn($q) => $q->where('nombre', $estado)))
        ->orderBy('nombre')
        ->paginate($cantidad);

    return view('clientes.index', compact('clientes', 'buscar', 'estado', 'cantidad'));
}
    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create()
    {
        $usuarios = User::where('id_cargo', 6)
        ->orWhereHas('cargo', function ($query) {
        $query->where('nombre', 'Director Ejecutivo');
        })
        ->get();

        $tiposDeContratos = TipoContrato::all();
        $estadosClientes = EstadoCliente::all();

        return view('clientes.create', compact('usuarios', 'estadosClientes', 'tiposDeContratos' ));
    }

    /**
     * Guarda un nuevo cliente en la base de datos.
     */

    public function store(Request $request)
    {
        // Validación de los datos
    $request->validate([
        'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'nombre' => 'required|string|max:150',
        'correo_electronico' => 'required|email|max:255',
        'telefono' => 'required|string|max:50',
        'sitio_web' => 'nullable|string|max:100',
        'usuario_id' => 'required|exists:users,id',
        'estadoCliente_id' => 'required|exists:estado_clientes,id',
        'tiposDeContratos' => 'required|array',
        'tiposDeContratos.*' => 'exists:tipo_contratos,id',
        'url_instagram' => 'nullable|url|max:255',
        'url_facebook' => 'nullable|url|max:255',
        'url_youtube' => 'nullable|url|max:255',
    ]);
        // Guardar dentro de una transacción
        DB::transaction(function () use ($request) {
            // Procesar logo si existe
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('logos_clientes', 'public');
            }

            // Crear cliente
            $cliente = Cliente::create([
                'logo' => $logoPath,
                'nombre' => $request->nombre,
                'correo_electronico' => $request->correo_electronico,
                'telefono' => $request->telefono,
                'sitio_web' => $request->sitio_web,
                'id_usuario' => $request->usuario_id,
                'id_estado_cliente' => $request->estadoCliente_id,
            ]);

            // Asociar contratos (tabla pivote)
            // Asociar contratos (tabla pivote)
    $cliente->tiposContrato()->sync($request->tiposDeContratos);


            // Registrar redes sociales sin duplicados
            $redes = [
                ['nombre_rsocial' => 'Instagram', 'url_rsocial' => $request->url_instagram],
                ['nombre_rsocial' => 'Facebook',  'url_rsocial' => $request->url_facebook],
                ['nombre_rsocial' => 'YouTube',   'url_rsocial' => $request->url_youtube],
            ];

            foreach ($redes as $red) {
                if (!empty($red['url_rsocial'])) {
                    $existe = $cliente->redSocial()
                        ->where('nombre_rsocial', $red['nombre_rsocial'])
                        ->exists();

                    if (!$existe) {
                        $cliente->redSocial()->create($red);
                    }
                }
            }
        });

        return redirect()->route('clientes.index')->with('success', 'Cliente creado exitosamente.');
    }


    /**
     * Muestra los detalles de un cliente.
     */
    public function show()
    {

    }

    /**
     * Muestra el formulario para editar un cliente.
     */
    public function edit(Cliente $cliente)
    {
        $usuarios = User::where('id_cargo', 6)
        ->orWhereHas('cargo', function ($query) {
            $query->where('nombre', 'Director Ejecutivo');
        })
        ->get();

        $tiposDeContratos = TipoContrato::all();
        $estadosClientes = EstadoCliente::all();
        return view('clientes.edit', compact('cliente', 'usuarios', 'estadosClientes', 'tiposDeContratos'));
    }

    /**
     * Actualiza la información del cliente.
     */
    public function update(Request $request, Cliente $cliente)
{
    // Validación de los datos
    $request->validate([
        'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'nombre' => 'required|string|max:150',
        'correo_electronico' => 'required|email|max:255',
        'telefono' => 'required|string|max:50',
        'sitio_web' => 'nullable|string|max:100',
        'usuario_id' => 'required|exists:users,id',
        'estadoCliente_id' => 'required|exists:estado_clientes,id',
        'tiposDeContratos' => 'required|array',
        'tiposDeContratos.*' => 'exists:tipo_contratos,id',
        'url_instagram' => 'nullable|url|max:255',
        'url_facebook' => 'nullable|url|max:255',
        'url_youtube' => 'nullable|url|max:255',
    ]);

    // Iniciar transacción para guardar los cambios
    DB::transaction(function () use ($request, $cliente) {
        // Procesar logo si existe y actualizarlo
        $logoPath = $cliente->logo;
        if ($request->hasFile('logo')) {
            // Si ya existe un logo, eliminamos el antiguo
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            // Guardamos el nuevo logo
            $logoPath = $request->file('logo')->store('logos_clientes', 'public');
        }

        // Actualizar cliente con los nuevos datos
        $cliente->update([
            'logo' => $logoPath,
            'nombre' => $request->nombre,
            'correo_electronico' => $request->correo_electronico,
            'telefono' => $request->telefono,
            'sitio_web' => $request->sitio_web,
            'id_usuario' => $request->usuario_id,
            'id_estado_cliente' => $request->estadoCliente_id,
        ]);

        // Actualizar contratos (tabla pivote)
        $cliente->tiposContrato()->sync($request->tiposDeContratos);

        // Registrar redes sociales (actualizar si ya existe o crear si es nuevo)
        $redes = [
            ['nombre_rsocial' => 'Instagram', 'url_rsocial' => $request->url_instagram],
            ['nombre_rsocial' => 'Facebook',  'url_rsocial' => $request->url_facebook],
            ['nombre_rsocial' => 'YouTube',   'url_rsocial' => $request->url_youtube],
        ];

        foreach ($redes as $red) {
            if (!empty($red['url_rsocial'])) {
                // Verificar si la red social ya existe para este cliente
                $redSocial = $cliente->redSocial()->where('nombre_rsocial', $red['nombre_rsocial'])->first();
                if ($redSocial) {
                    // Si existe, actualizar
                    $redSocial->update(['url_rsocial' => $red['url_rsocial']]);
                } else {
                    // Si no existe, crear
                    $cliente->redSocial()->create($red);
                }
            }
        }
    });

    return redirect()->route('clientes.index')->with('success', 'Cliente actualizado exitosamente.');
}


    /**
     * Elimina un cliente de la base de datos.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado exitosamente.');
    }

}