<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ url('/equipo/usuarios') }}" class="btn btn-primary">Listado de usuarios</a>
    </x-slot>

    <x-slot name="titulo">
        Editar usuario
    </x-slot>
    <x-slot name="slot">
        <div class="container-fluid">
            <div class="row" style="height: auto;">
                <div class="col-md-6">
                    <form action="{{ route('equipo.usuarios.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            {{-- Logo cliente --}}
                        <div class="col-md-12 mb-3">
                            <label for="logo">Imagen de perfil</label>
                            <input type="file" name="foto_perfil" id="foto_perfil" class="form-control @error('foto_perfil') form-control-warning @enderror">
                            <small class="text-muted">Imagen del cliente (JPG, PNG, WEBP, máx. 2MB).</small>
                            @error('foto_perfil') <div class="text-warning">{{ $message }}</div> @enderror
                            
                            @if($user->foto_perfil)
                                <img src="{{ asset('storage/' . $user->foto_perfil) }}" alt="Foto de perfil" class="mt-2 d-block" style="max-width: 100px;">
                            @endif
                        </div>
                            {{-- Campo: Nombre --}}
                            <div class="col-md-6 mb-3">
                                <label for="name" class="text-black">Nombre completo:<span class="text-danger">*</span></label>
                                <input name="name" type="text" class="form-control" required value="{{ old('name', $user->name) }}">
                                @error('name') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Campo: Email --}}
                            <div class="col-md-6 mb-3">
                                <label for="email" class="text-black">Email:<span class="text-danger">*</span></label>
                                <input name="email" type="email" class="form-control" required value="{{ old('email', $user->email) }}">
                                @error('email') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>
                            {{-- Campo: Contraseña (solo si deseas cambiarla) --}}
                            <div class="col-md-6 mb-3">
                                <label for="password" class="text-black">Cambiar contraseña:</label>
                                <input name="password" type="password" class="form-control" disabled>
                                @error('password') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Selector: Cargo --}}
                            <div class="col-md-6 mb-3">
                                <label for="id_cargo" class="text-black">Cargo:<span class="text-danger">*</span></label>
                                <select name="id_cargo" class="form-control" required>
                                    <option disabled selected>Seleccione un cargo</option>
                                    @foreach ($cargos as $cargo)
                                        <option value="{{ $cargo->id }}" {{ old('id_cargo', $user->id_cargo) == $cargo->id ? 'selected' : '' }}>
                                            {{ $cargo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_cargo') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Teléfono --}}
                            <div class="col-md-6 mb-3">
                                <label for="telefono" class="text-black">Teléfono:<span class="text-danger">*</span></label>
                                <input name="telefono" type="text" class="form-control" required value="{{ old('telefono', $user->telefono) }}">
                                @error('telefono') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>
                            {{-- Fecha de nacimiento --}}
                            <div class="col-md-6 mb-3">
                                <label for="f_nacimiento" class="text-black">Fecha de nacimiento:<span class="text-danger">*</span></label>
                                <input name="f_nacimiento" type="date" class="form-control" required value="{{ old('f_nacimiento', $f_nacimiento_formateada) }}">
                                @error('f_nacimiento') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Rol --}}
                            <div class="col-md-6 mb-3">
                                <label for="role" class="text-black">Rol:<span class="text-danger">*</span></label>
                                <select name="role" class="form-control" required>
                                    <option disabled selected>Seleccione un rol</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role', $user->roles->first()?->id) == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('role') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Área --}}
                            <div class="col-md-6 mb-3">
                                <label for="id_area" class="text-black">Área:<span class="text-danger">*</span></label>
                                <select name="id_area" class="form-control" required>
                                    <option disabled selected>Seleccione un área</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}" {{ old('id_area', $user->id_area) == $area->id ? 'selected' : '' }}>
                                            {{ $area->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_area') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            </div>

                            {{-- Botón de envío --}}
                            <div class="col-md-12">
                                <input type="submit" class="btn btn-success col-12 btn-lg" value="Actualizar usuario">
                            </div>
                        </div>
                    </form>
                </div>
                {{-- Imagen decorativa --}}
                <div class="col-md-6 d-flex p-0 align-items-center justify-content-center" style="background-color: #003B7B;">
                    <img src="{{ asset('img/cee2bd3f9f.png') }}" alt="Logo Himalaya" class="img-fluid" style="max-width: 100%; height: auto;">
                </div>
            </div>
        </div>
    </x-slot>
</x-app-layout>