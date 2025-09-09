<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ModalidadController;
use App\Http\Controllers\HerramientaController;
use App\Http\Controllers\MapaClienteController;
use App\Http\Controllers\FaseServicioController;
use App\Http\Controllers\TipoServicioController;
use Spatie\Permission\Middleware\PermissionMiddleware;
use App\Http\Controllers\FasesServicioInstanciaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Configuracion\ServiciosConfigController;

Route::get('/', function () {
    return view('auth.login');
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


//__/ÁREAS/__//

// Consultar áreas
Route::middleware(['auth'])
    ->get('/equipo/areas', [AreaController::class, 'index'])
    ->name('equipo.areas.index');

// Registrar área (crear y guardar)
Route::middleware(['auth'])
    ->group(function () {
        Route::get('/equipo/areas/create', [AreaController::class, 'create'])->name('equipo.areas.create');
        Route::post('/equipo/areas', [AreaController::class, 'store'])->name('equipo.areas.store');
    });

// Modificar área (editar y actualizar)
Route::middleware(['auth'])
    ->group(function () {
        Route::get('/equipo/areas/{area}/edit', [AreaController::class, 'edit'])->name('equipo.areas.edit');
        Route::put('/equipo/areas/{area}', [AreaController::class, 'update'])->name('equipo.areas.update');
    });

// Eliminar área
Route::middleware(['auth'])
    ->delete('/equipo/areas/{area}', [AreaController::class, 'destroy'])
    ->name('equipo.areas.destroy');

//__/CARGOS/__//

//consultar cargos
Route::middleware(['auth'])
    ->get('/equipo/cargos', [CargoController::class, 'index'])
    ->name('equipo.cargos.index');

//registrar cargo (crear y guardar)
Route::middleware(['auth'])
    ->group(function () {
        Route::get('/equipo/cargos/create', [CargoController::class, 'create'])->name('equipo.cargos.create');
        Route::post('/equipo/cargos', [CargoController::class, 'store'])->name('equipo.cargos.store');
    });

// Modificar cargo (editar y actualizar)
Route::middleware(['auth'])->group(function () {
    Route::get('/equipo/cargos/{cargo}/edit', [CargoController::class, 'edit'])->name('equipo.cargos.edit');
    Route::put('/equipo/cargos/{cargo}', [CargoController::class, 'update'])->name('equipo.cargos.update');
});

// Eliminar cargo
Route::middleware(['auth', PermissionMiddleware::class . ':eliminar cargo'])
    ->delete('/equipo/cargos/{cargo}', [CargoController::class, 'destroy'])
    ->name('equipo.cargos.destroy');

//__/ROLES/__//

//registrar role (crear y guardar)
Route::middleware(['auth'])->group(function () {
    Route::get('/equipo/roles/create', [RoleController::class, 'create'])->name('equipo.roles.create');
    Route::post('/equipo/roles', [RoleController::class, 'store'])->name('equipo.roles.store');
});

// Modificar role (editar y actualizar)
Route::middleware(['auth'])->group(function () {
    Route::get('/equipo/roles/{role}/edit', [RoleController::class, 'edit'])->name('equipo.roles.edit');
    Route::put('/equipo/roles/{role}', [RoleController::class, 'update'])->name('equipo.roles.update');
});

//Consultar role
Route::middleware(['auth'])
    ->get('/equipo/roles', [RoleController::class, 'index'])->name('equipo.roles.index');


// Eliminar role
Route::middleware(['auth'])
    ->delete('/equipo/cargos/{role}', [RoleController::class, 'destroy'])
    ->name('equipo.roles.destroy');


//__/USUARIOS/__//

//registrar usuario (crear y guardar)
Route::middleware(['auth', PermissionMiddleware::class . ':registrar usuario'])->group(function () {
    Route::get('/equipo/usuarios/create', [GeneralController::class, 'create'])->name('equipo.usuarios.create');
    Route::post('/equipo/usuarios', [GeneralController::class, 'store'])->name('equipo.usuarios.store');
});

// Modificar usuario (editar y actualizar)
Route::middleware(['auth'])->group(function () {
    Route::get('/equipo/usuarios/{user}/edit', [GeneralController::class, 'edit'])->name('equipo.usuarios.edit');
    Route::put('/equipo/usuarios/{user}', [GeneralController::class, 'update'])->name('equipo.usuarios.update');
});

//Consultar usuario
Route::middleware(['auth'])->get('/equipo/usuarios', [GeneralController::class, 'index'])->name('equipo.usuarios.index');

// Eliminar usuario
Route::middleware(['auth'])->delete('/equipo/usuarios/{user}', [GeneralController::class, 'destroy'])->name('equipo.usuarios.destroy');

//Asignar permisos
Route::middleware(['auth'])->group(function () {
    Route::get('/permisos', [PermisoController::class, 'asignarPermisos'])->name('permisos.index');
    Route::post('/permisos/{roleId}/permissions/update', [PermisoController::class, 'updatePermissions'])->name('permisos.updatePermissions')->where('roleId', '[0-9]+');
});

// Perfil
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//__/CLIENTE/__//

//registrar cliente (crear y guardar)
Route::middleware(['auth'])->group(function () {
    Route::get('/clientes/create', [ClienteController::class, 'create'])->name('clientes.create');
    Route::post('/clientes', [ClienteController::class, 'store'])->name('clientes.store');
});

//Modificar cliente (editar y actualizar)
Route::middleware(['auth'])->group(function () {
    Route::get('/clientes/{cliente}/edit', [ClienteController::class, 'edit'])->name('clientes.edit');
    Route::put('/clientes/{cliente}', [ClienteController::class, 'update'])->name('clientes.update');
});

//Consultar cliente
Route::middleware(['auth'])->get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');

//Eliminar cliente
Route::middleware(['auth'])->delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])->name('clientes.destroy');


//__/HERRAMIENTAS/__//

Route::middleware(['auth', 'verified'])->get('/herramientas', [HerramientaController::class, 'index'])->name('herramientas.index');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/modalidades', [ModalidadController::class, 'index']);

    Route::get('/tipos-servicio', [TipoServicioController::class, 'index']);
    Route::post('/tipos-servicio', [TipoServicioController::class, 'store']);
    Route::put('/tipos-servicio/{tipo}', [TipoServicioController::class, 'update'])->whereNumber('tipo');
    Route::delete('/tipos-servicio/{tipo}', [TipoServicioController::class, 'destroy'])->whereNumber('tipo');

    Route::get('/fases-servicio', [FaseServicioController::class, 'index']);
    Route::post('/fases-servicio', [FaseServicioController::class, 'store']);
    Route::put('/fases-servicio/{fase}', [FaseServicioController::class, 'update'])->whereNumber('fase');
    Route::delete('/fases-servicio/{fase}', [FaseServicioController::class, 'destroy'])->whereNumber('fase');
});


//ERRORS VIEWS

Route::get('400', function () {
    return view('errors.400');
})->name('400');

Route::get('404', function () {
    return view('errors.404');
})->name('404');

Route::get('403', function () {
    return view('errors.403');
})->name('403');

Route::get('419', function () {
    return view('errors.419');
})->name('419');

Route::get('500', function () {
    return view('errors.500');
})->name('500');

Route::get('503', function () {
    return view('errors.503');
})->name('503');


Route::prefix('configuracion')->middleware(['auth', 'verified'])->group(function () {

    // =========================
    // SERVICIOS (CRUD por cliente)
    // =========================

    Route::prefix('servicios')->group(function () {
        Route::get('/{cliente}', [ServiciosConfigController::class, 'index'])
            ->whereNumber('cliente')->name('config.servicios.index');

        Route::get('/{cliente}/create', [ServiciosConfigController::class, 'create'])
            ->whereNumber('cliente')->name('config.servicios.create');

        Route::post('/{cliente}', [ServiciosConfigController::class, 'store'])
            ->whereNumber('cliente')->name('config.servicios.store');

        Route::get('/{cliente}/{servicio}/edit', [ServiciosConfigController::class, 'edit'])
            ->whereNumber(['cliente', 'servicio'])->name('config.servicios.edit');

        Route::put('/{cliente}/{servicio}', [ServiciosConfigController::class, 'update'])
            ->whereNumber(['cliente', 'servicio'])->name('config.servicios.update');

        Route::delete('/{cliente}/{servicio}', [ServiciosConfigController::class, 'destroy'])
            ->whereNumber(['cliente', 'servicio'])->name('config.servicios.destroy');

        // ====== AJAX (dentro del mismo controlador) ======
        Route::get('/ajax/modalidades/{modalidad}/tipos', [ServiciosConfigController::class, 'ajaxTiposPorModalidad'])
            ->whereNumber('modalidad')->name('config.servicios.ajax.tipos');

        Route::get('/ajax/tipos/{tipo}/fases', [ServiciosConfigController::class, 'ajaxFasesPorTipo'])
            ->whereNumber('tipo')->name('config.servicios.ajax.fases');
    });

    // ================================================
    // FASES POR SERVICIO (INSTANCIAS + DnD)
    // ================================================
    Route::prefix('servicios/{cliente}/{servicio}/fases')
        ->whereNumber(['cliente', 'servicio'])
        ->group(function () {
            // Listado (ordenadas por posicion)
            Route::get('', [FasesServicioInstanciaController::class, 'index'])
                ->name('config.servicios.fases.index');

            // Crear instancia
            Route::post('', [FasesServicioInstanciaController::class, 'store'])
                ->name('config.servicios.fases.store');

            // Actualizar nombre/descripcion
            Route::put('{fase}', [FasesServicioInstanciaController::class, 'update'])
                ->whereNumber('fase')->name('config.servicios.fases.update');

            // Eliminar
            Route::delete('{fase}', [FasesServicioInstanciaController::class, 'destroy'])
                ->whereNumber('fase')->name('config.servicios.fases.destroy');

            // Drag & Drop (reordenar)
            Route::post('reordenar', [FasesServicioInstanciaController::class, 'reordenar'])
                ->name('config.servicios.fases.reordenar');
        });

    // ============================================
    // MAPA DEL CLIENTE (Horas por área del servicio)
    // ============================================
    Route::prefix('servicios/{cliente}/{servicio}/mapa')
        ->whereNumber(['cliente', 'servicio'])
        ->group(function () {

            // Mostrar mapa + filas de áreas
            Route::get('', [MapaClienteController::class, 'show'])
                ->name('config.servicios.mapa.show');

            // Upsert en bloque de horas por área
            Route::post('areas/upsert', [MapaClienteController::class, 'upsertAreas'])
                ->name('config.servicios.mapa.areas.upsert');

            // Eliminar una fila (área) del mapa
            Route::delete('areas/{area}', [MapaClienteController::class, 'destroyArea'])
                ->whereNumber('area')->name('config.servicios.mapa.areas.destroy');
        });
});





require __DIR__ . '/auth.php';