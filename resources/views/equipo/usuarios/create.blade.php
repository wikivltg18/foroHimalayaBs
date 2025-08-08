<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/usuarios') }}" class="btn btn-primary">Listado de usuarios</a></a>
    </x-slot>

    <!-- Título principal -->
    <x-slot name="titulo">
        Crear usuarios
    </x-slot>
    
    <x-slot name="slot">
        <!-- Contenedor de fila con dos columnas -->
        {{-- Contenedor principal del formulario de registro de usuario --}}
<div class="container-fluid" style="padding-bottom: 5rem;">
        <div class="row" style="height: 360px;">
        {{-- Columna izquierda: Formulario de registro --}}
        <div class="col-md-6">
            <form action="{{route('equipo.usuarios.store')}}" method="POST" enctype="multipart/form-data">
                <div class="row">
                    @csrf
                    {{-- Foto de perfil --}}
                        <div class="col-md-12 mb-3">
                            <label for="logo">Imagen de perfil:</label>
                            <input type="file" name="foto_perfil" id="foto_perfil" class="form-control @error('foto_perfil') form-control-warning @enderror">
                            @error('foto_perfil') <div class="text-warning">{{ $message }}</div> @enderror

                        </div>
                    {{-- Campo: Nombres del usuario --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="name" class="text-black">Nombre completo:<span class="text-danger">*</span></label>
                            <input name="name" type="text" class="form-control" required value="{{old('name')}}">
                        </div>
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
                            <input name="email" type="email" class="form-control" required value="{{old('email')}}">
                        </div>
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
                            <input name="password" type="password" class="form-control" required >
                        </div>
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
                        @error('id_rol')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Campo: Teléfono --}}
                    <div class="col-md-6 mb-3">
                        <div class="form-group">
                            <label for="telefono" class="text-black">Telefono:<span class="text-danger">*</span></label>
                            <input name="telefono" type="text" class="form-control" required value="{{ old('telefono')}}">
                        </div>
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
                            <input name="f_nacimiento" type="date" class="form-control" required value="{{old('f_nacimiento')}}">
                        </div>
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
                            <select name="role" class="form-control" required>
                                <option value="" disabled selected>Seleccione un rol</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>

                        </div>
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
                            <select name="id_area" id="" class="form-control" required>
                                <option value="" disabled selected >Seleccione un área</option>
                                @foreach ($areas as  $area)
                                <option value="{{ $area->id }}" {{ old('id_area') == $area->id ? 'selected' : '' }}>{{ $area->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('id_area')
                        <div class="alert alert-danger" id="error-alert">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    {{-- Botón para registrar nuevo usuario --}}
                    <div class="col-md-12">
                        <input type="submit" class="btn btn-success col-12 btn-lg"  value="Registrar nuevo usuario">
                    </div>
                </div>
            </form>
        </div>
        {{-- Columna derecha: Imagen decorativa/logo --}}
                <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #003B7B;">
                    <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="Logo Himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
                </div>
        </div>
    </x-slot>
</x-app-layout>