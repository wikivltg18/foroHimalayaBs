<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\User;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreUserRequest;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateUserRequest;

class GeneralController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //Evalua si el usuario autenticado tiene el permiso de consultar usuarios
        if (!Auth::user()->can('consultar usuarios')) {
        return redirect()->route('dashboard')->with('error', 'No tienes acceso a este módulo.');
    }

        // Sanitiza el término de búsqueda para evitar que los caracteres especiales afecten la consulta SQL
        $busqueda = str_replace(['%', '_'], ['\%', '\_'], $request->input('buscar'));

        // Obtiene los usuarios junto con sus relaciones: cargo, rol y área
        $users = User::with(['cargo', 'role', 'area']) 
        // Aplica un filtro condicional si hay un término de búsqueda
            ->when($busqueda, function ($query, $busqueda) {
        // Filtra los usuarios cuyo nombre coincida parcialmente con el término de búsqueda
                $query->where('name', 'like', "%{$busqueda}%");
            })
            // Ordena los resultados alfabéticamente por nombre
            ->orderBy('name')
            // Pagina los resultados, mostrando 5 usuarios por página
            ->paginate(5)
            // Mantiene el término de búsqueda en la URL durante la paginación
            ->appends(['buscar' => $busqueda]);
            // Devuelve la vista con los datos de usuarios y el término de búsqueda actual
        return view('equipo.usuarios.index', compact('users', 'busqueda'));
    }

    /**
     * Show the form for creating a new resource.
     */

        // Muestra el formulario de creación de usuarios
    public function create()
    {
        // Obtiene todos los registros de la tabla 'cargos'
        $cargos = Cargo::all();

        // Obtiene todos los registros de la tabla 'roles'
        $roles = Role::all();

        // Obtiene todos los registros de la tabla 'areas'
        $areas = Area::all();

        // Retorna la vista del formulario, pasando los datos recopilados
        return view('equipo.usuarios.create', compact('cargos', 'roles', 'areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
// Almacena un nuevo usuario en la base de datos
public function store(StoreUserRequest $request)
{
    // Obtiene el ID del rol desde la solicitud
    $roleId = $request->input('role');

    // Busca el rol correspondiente en la base de datos, lanza excepción si no existe
    $role = Role::findOrFail($roleId); // obtener nombre del rol

    // Extrae solo los campos relevantes del formulario
    $data = $request->only(['foto_perfil','name', 'email', 'password', 'telefono', 'f_nacimiento']);

    $fotoPath = null;
            if ($request->hasFile('foto_perfil')) {
                $fotoPath = $request->file('foto_perfil')->store('foto_perfil_usuarios', 'public');
            }
    $data['foto_perfil'] = $fotoPath;
    // Encripta la contraseña antes de guardarla
    $data['password'] = bcrypt($data['password']);

    // Asigna un valor por defecto de horas, podría representar jornada laboral
    $data['h_defecto'] = 160;

    // Asigna las relaciones de área, cargo y rol al usuario
    $data['id_area'] = $request->input('id_area');
    $data['id_cargo'] = $request->input('id_cargo');
    $data['id_rol'] = $roleId; // se guarda ID si tu modelo lo requiere

    // Crea el usuario con los datos recopilados
    $user = User::create($data);

    // Asigna el rol usando su nombre, si estás usando Spatie o similar
    $user->assignRole($role->name); // asignar por nombre

    // Redirecciona al listado de usuarios con un mensaje de éxito
    return redirect()->route('equipo.usuarios.index')->with('success', 'Usuario creado exitosamente.');
}
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */

    // Muestra el formulario para editar un usuario existente
    public function edit(User $user)
    {
        // Verifica si el usuario autenticado tiene el permiso para modificar usuarios
        if (!Auth::user()->can('modificar usuario')) {
        // Redirecciona al dashboard con un mensaje de error si no tiene acceso
            return redirect()->route('dashboard')->with('error', 'No tienes acceso para editar usuarios.');
        }

        // Formatea la fecha de nacimiento del usuario a la zona horaria de Bogotá y al formato 'YYYY-MM-DD'
        $f_nacimiento_formateada = optional($user->f_nacimiento)
            ->setTimezone('America/Bogota')
            ->format('Y-m-d');

        // Obtiene todos los registros de las tablas necesarias para los campos del formulario
        $cargos = Cargo::all(); // Cargos disponibles
        $roles = Role::all();   // Roles disponibles
        $areas = Area::all();   // Áreas disponibles

        // Retorna la vista del formulario de edición, pasando los datos requeridos
        return view('equipo.usuarios.edit', compact('user','f_nacimiento_formateada',  'cargos', 'roles', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
        // Actualiza los datos de un usuario existente
    public function update(UpdateUserRequest $request, User $user)
    {
        // Verifica si el usuario autenticado tiene permiso para modificar usuarios
        if (!Auth::user()->can('modificar usuario')) {
            // Si no tiene el permiso, redirige al dashboard con un mensaje de error
            return redirect()->route('dashboard')->with('error', 'No tienes acceso para modificar usuarios.');
        }


        // Obtiene el ID del rol desde la solicitud
        $roleId = $request->input('role');

        // Busca el rol correspondiente por ID, lanza excepción si no se encuentra
        $role = Role::findOrFail($roleId);

        // Extrae los campos necesarios del formulario
        $data = $request->only(['foto_perfil','name', 'email', 'password', 'telefono', 'f_nacimiento']);

        // Procesar foto si existe y actualizarlo
        $fotoPath = $user->foto_perfil;
        if ($request->hasFile('foto_perfil')) {
            // Si ya existe un foto, eliminamos el antiguo
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            // Guardamos el nuevo foto
            $fotoPath = $request->file('foto_perfil')->store('foto_perfil_usuarios', 'public');
        }

        $data['foto_perfil'] = $fotoPath;
        // Asigna un valor por defecto a las horas (posiblemente jornada laboral)
        $data['h_defecto'] = 160;

        // Asigna relaciones de área, cargo y rol al usuario
        $data['id_area'] = $request->input('id_area');
        $data['id_cargo'] = $request->input('id_cargo');
        $data['id_rol'] = $roleId;

        // Verifica si se ha ingresado una nueva contraseña y la encripta
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->input('password'));
        }
        
        // Actualiza el usuario con los datos recopilados
        $user->update($data);

        // Sincroniza el rol del usuario usando el nombre del rol (útil con paquetes como Spatie)
        $user->syncRoles([$role->name]);

        // Redirige al listado de usuarios con un mensaje de éxito
        return redirect()->route('equipo.usuarios.index')->with('success', 'Usuario actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */

        // Elimina un usuario del sistema
    public function destroy(User $user)
    {
        // Verifica si el usuario autenticado tiene permiso para eliminar usuarios
        if (!Auth::user()->can('eliminar usuario')) {
            // Si no tiene el permiso, redirige al dashboard con un mensaje de error
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para eliminar usuarios.');
        }

        // Elimina el usuario especificado
        $user->delete();

        // Redirige al listado de usuarios con un mensaje de éxito
        return redirect()->route('equipo.usuarios.index')->with('success', 'Usuario eliminado.');
    }
}