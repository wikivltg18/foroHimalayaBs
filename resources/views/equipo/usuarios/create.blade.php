<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/usuarios') }}" class="btn btn-primary">Listado de usuarios</a>
    </x-slot>

    <!-- Título principal -->
    <x-slot name="titulo">
        Crear usuarios
    </x-slot>
    
    <x-slot name="slot">
        <!-- Contenedor de fila con dos columnas -->
        {{-- Contenedor principal del formulario de registro de usuario --}}
<div class="container-fluid">
        <div class="row" style="height: auto;">
        {{-- Columna izquierda: Formulario de registro --}}
        <div class="col-md-6">
            <form action="{{route('equipo.usuarios.store')}}" method="POST" enctype="multipart/form-data">
                <div class="row">
                    @csrf
                    {{-- Foto de perfil --}}
                        <div class="col-md-12 mb-3">
                            <label for="logo">Imagen de perfil:</label>
                            {{-- Foto de perfil: añade accept --}}
                            <input type="file" name="foto_perfil" id="foto_perfil"
                                accept="image/jpeg,image/png,image/webp"
                                class="form-control @error('foto_perfil') form-control-warning @enderror">
                            <small class="text-muted">
                                Imagen (JPG/PNG/WEBP), máx. 2MB.
                            </small>
                            @error('foto_perfil') 
                                <div class="text-warning">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    {{-- Campo: Nombres del usuario --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="name" class="text-black">Nombre completo:<span class="text-danger">*</span></label>
                            <input name="name" type="text" class="form-control" required value="{{old('name')}}" id="name">
                        </div>
                        <div id="error-name"></div>
                        <small class="text-muted">Ej: Carlos Stiven Viveros.</small>
                        @error('name')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Campo: Email --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="email"class="text-black">Email:<span class="text-danger">*</span></label>
                            <input name="email" type="email" class="form-control" required value="{{old('email')}}" id="email">
                        </div>
                        <div id="errEmail"></div>
                        <small class="text-muted">
                            Ej: soporte@himalaya.digital.
                        </small>
                        @error('email')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Campo: Contraseña --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="password"class="text-black">Password:<span class="text-danger">*</span></label>
                            <input name="password" type="password" class="form-control" id="password" required >
                        </div>
                        <div id="errorPassword"></div>
                        <small class="text-muted">
                            Mínimo 8 caracteres.
                        </small>
                        @error('password')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Selector: Cargo --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="id_cargo" class="text-black">Cargo a asignar:<span class="text-danger">*</span></label>
                            <select name="id_cargo" id="id_cargo" class="form-control" required>
                                <option value="" disabled selected>Seleccione un cargo</option>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo->id }}" {{ old('id_cargo') == $cargo->id ? 'selected' : '' }}>
                                        {{ $cargo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">
                            Ej: Diseñador, Desarrollador, etc.
                        </small>
                        @error('id_cargo')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    {{-- Campo: Teléfono --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="telefono" class="text-black">Telefono:<span class="text-danger">*</span></label>
                            <input name="telefono" type="text" class="form-control" required value="{{ old('telefono')}}" id="telefono">
                        </div>
                        <small class="text-muted">
                            Ej: 3001234567.
                        </small>
                        @error('telefono')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Campo: Fecha de nacimiento --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="f_nacimiento" class="text-black">Fecha de nacimiento:<span class="text-danger">*</span></label>
                            <input name="f_nacimiento" type="date" class="form-control" required value="{{old('f_nacimiento')}}" id="f_nacimiento">
                        </div>
                        <small class="text-muted">
                            Ej: 20/05/1990.
                        </small>
                        @error('f_nacimiento')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Campo: Rol a asignar (select) --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="id_rol" class="text-black">Rol a asignar:<span class="text-danger">*</span></label>
                            <select name="role" class="form-control" id="id_role" required>
                                <option value="" disabled selected>Seleccione un rol</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">
                            Ej: Administrador, Superadministrador, etc.
                        </small>
                        @error('id_rol')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Campo: Área a asignar (select) --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="id_area" class="text-black">Área a asignar:<span class="text-danger">*</span></label>
                            <select name="id_area" class="form-control" id="id_area" required>
                                <option value="" disabled selected >Seleccione un área</option>
                                @foreach ($areas as  $area)
                                <option value="{{ $area->id }}" {{ old('id_area') == $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <small class="text-muted">
                            Ej: Diseño, Contenido.
                        </small>
                        @error('id_area')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Botón para registrar nuevo usuario --}}
                    <div class="col-md-12">
                        <input type="submit" class="btn btn-success col-12 btn-lg" id="btnGuardar" value="Registrar nuevo usuario">
                    </div>
                </div>
            </form>
        </div>
        {{-- Columna derecha: Imagen decorativa/logo --}}
            <div class="col-md-6 p-0 d-flex align-items-center justify-content-center" style="background-color: #003B7B;">
                <img src="{{ asset('img/cee2bd3f9f.png') }}" alt="Logo Himalaya" class="img-fluid" style="max-width: 100%; height: auto;">
            </div>
        </div>
    </x-slot>
    @push('scripts')
        <script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Validadores ---
    function validarEmailValor(valor) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(valor || '');
    }
    function validarContraseñaValor(valor) {
        // Min 8, una mayúscula, una minúscula, un número y un carácter especial
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
        return regex.test(valor || '');
    }

    function limpiarNoDigitos(str) {
        // Remplaza letras por vacios, solo permite numeros
        return (str || '').replace(/\D+/g, '');
    }

    // --- Elementos ---
    const nombreInput   = document.getElementById('name');
    const emailInput    = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const telefonoInput = document.getElementById('telefono');
    const fNacimiento   = document.getElementById('f_nacimiento');
    const fotoPerfil    = document.getElementById('foto_perfil');
    const idCargoSelect = document.getElementById('id_cargo');
    const idRolSelect   = document.getElementById('id_role');  // <-- coincide con el select
    const idAreaSelect  = document.getElementById('id_area');

    const errName = document.getElementById('errName');
    const errEmail = document.getElementById('errEmail');
    const errorPassword = document.getElementById('errorPassword');

    const btnGuardar    = document.getElementById('btnGuardar');

    // --- Listeners ---
    if (nombreInput) {
        nombreInput.addEventListener('input', () => {
            const v = nombreInput.value();
            const invalido = v === ''; 
            toggleInvalido(nombreInput, invalido);
            setError(errName, invalido ? 'El nombre no es válido.' : ''); 
            verificarFormulario();
        });
    }

    if (emailInput) {
        emailInput.addEventListener('input', () => {
            const v = emailInput.value.trim();
            const valido = v && validarEmailValor(v);
            toggleInvalido(emailInput, !valido);
            setError(errEmail, valido ? '' : 'El formato del email no es válido.');
            verificarFormulario();
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener('input', () => {
            const v = passwordInput.value.trim();
            const valido = v && validarContraseñaValor(v);
            toggleInvalido(passwordInput, !valido);
            setError(errorPassword, valido ? '' : 'La constraseña debe tener Min 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial');
            verificarFormulario();
        });
    }

    if (telefonoInput) {
        telefonoInput.addEventListener('input', () => {
            const limpio = limpiarNoDigitos(telefonoInput.value);
            if (telefonoInput.value !== limpio) {
                telefonoInput.value = limpio;
            }
            verificarFormulario();
        })            
    }

    if (fNacimiento) {
        const hoy = new Date();
        // Calcular la fecha hace 18 años
        const fechaLimite = new Date(hoy.getFullYear() - 18, hoy.getMonth(), hoy.getDate());
        // Formatear la fecha en formato YYYY-MM-DD
        const formatoFecha = fechaLimite.toISOString().split('T')[0];
        fNacimiento.value = formatoFecha;
        fNacimiento.max = formatoFecha;

         // Validación al cambiar el valor
        fNacimiento.addEventListener('input', () => {
            fNacimiento.classList.toggle('is-invalid', fNacimiento.value === '');
            verificarFormulario();
        });
        
    }

    if (fotoPerfil) {
        fotoPerfil.addEventListener('change', () => {
            const file = fotoPerfil.files?.[0];
            if (file) {
                const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
                const isValidType = validTypes.includes(file.type);
                const isValidSize = file.size <= 2 * 1024 * 1024; // 2MB
                fotoPerfil.classList.toggle('is-invalid', !isValidType || !isValidSize);
            } else {
                fotoPerfil.classList.remove('is-invalid');
            }
            verificarFormulario();
        });
    }

    function toggleInvalido(el, invalido) {
        if (!el) return;
        el.classList.toggle('is-invalid', !!invalido);
    }
    function setError(container, msg) {
        if (!container) return;
        container.innerHTML = msg ? `<p class="text-danger mb-1">${msg}</p>` : '';
    }

    if (idCargoSelect) idCargoSelect.addEventListener('change', verificarSelectRequerido);
    if (idRolSelect)   idRolSelect.addEventListener('change', verificarSelectRequerido);
    if (idAreaSelect)  idAreaSelect.addEventListener('change', verificarSelectRequerido);

    function verificarSelectRequerido(e) {
        const el = e.currentTarget;
        el.classList.toggle('is-invalid', !el.value);
        verificarFormulario();
    }

    // --- Chequeos globales ---
    function camposRequeridosValidos() {
        const requeridos = [
            nombreInput,
            emailInput,
            passwordInput,
            telefonoInput,
            fNacimiento,
            idCargoSelect,
            idRolSelect,
            idAreaSelect
        ].filter(Boolean); // quita nulos por si algún campo no existe

        const algunVacio = requeridos.some(el =>
            el.tagName === 'SELECT' ? !el.value : el.value.trim() === ''
        );

        const algunInvalido = requeridos.some(el => el.classList.contains('is-invalid'));

        // Foto de perfil es opcional; si la quieres obligatoria, añádela a "requeridos"
        return !algunVacio && !algunInvalido;
    }

    function verificarFormulario() {
        const ok = camposRequeridosValidos();
        if (btnGuardar) btnGuardar.disabled = !ok;
    }

    // Estado inicial
    verificarFormulario();
});
</script>

    @endpush
</x-app-layout>