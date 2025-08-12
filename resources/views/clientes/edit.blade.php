<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('clientes.index') }}" class="btn btn-primary">Listado de clientes</a>
    </x-slot>

    <x-slot name="titulo">
        Editar cliente
    </x-slot>

    <x-slot name="slot">
        <div class="row">
            {{-- Columna izquierda: Formulario --}}
            <div class="col-md-6">
                <form action="{{ route('clientes.update', $cliente->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $instagram = optional($cliente->redSocial->firstWhere('nombre_rsocial','Instagram'))->url_rsocial;
                        $facebook  = optional($cliente->redSocial->firstWhere('nombre_rsocial','Facebook'))->url_rsocial;
                        $youtube   = optional($cliente->redSocial->firstWhere('nombre_rsocial','YouTube'))->url_rsocial;
                    @endphp

                    <div class="row">
                        {{-- Logo cliente --}}
                        <div class="col-md-6 mb-3">
                            <label for="logo">Logo del cliente</label>
                            <input type="file" name="logo" id="logo" class="form-control @error('logo') form-control-warning @enderror">
                            <small class="text-muted">Imagen del cliente (JPG, PNG, WEBP, máx. 2MB).</small>
                            @error('logo') <div class="text-warning">{{ $message }}</div> @enderror

                            @if($cliente->logo)
                                <img src="{{ asset('storage/' . $cliente->logo) }}" alt="Logo del cliente" class="mt-2" style="max-width: 100px;">
                            @endif
                        </div>

                        {{-- Nombre cliente --}}
                        <div class="col-md-6 mb-3">
                            <label for="nombre">Nombre del cliente <span class="text-danger">*</span></label>
                            <input type="text" id="nombre" name="nombre" class="form-control @error('nombre') form-control-warning @enderror" required value="{{ old('nombre', $cliente->nombre) }}">
                            <small class="text-muted">Ej: Unicentro, Manitoba, Comfandi.</small>
                            @error('nombre') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-md-6 mb-3">
                            <label for="correo_electronico">Email <span class="text-danger">*</span></label>
                            <input type="email" id="correo_electronico" name="correo_electronico" class="form-control @error('correo_electronico') form-control-warning @enderror" required value="{{ old('correo_electronico', $cliente->correo_electronico) }}">
                            <small class="text-muted">Ej: cliente@ejemplo.com</small>
                            @error('correo_electronico') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div class="col-md-6 mb-3">
                            <label for="telefono">Teléfono <span class="text-danger">*</span></label>
                            <input type="text" id="telefono" name="telefono" inputmode="tel" class="form-control @error('telefono') form-control-warning @enderror" required value="{{ old('telefono', $cliente->telefono) }}">
                            <small class="text-muted">Teléfono directo del cliente.</small>
                            @error('telefono') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Sitio web --}}
                        <div class="col-md-6 mb-3">
                            <label for="sitio_web">Sitio web</label>
                            <input type="url" id="sitio_web" name="sitio_web" class="form-control @error('sitio_web') form-control-warning @enderror" value="{{ old('sitio_web', $cliente->sitio_web) }}">
                            <small class="text-muted">URL del sitio web del cliente (opcional).</small>
                            @error('sitio_web') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Estado cliente --}}
                        <div class="col-md-6 mb-3">
                            <label for="estadoCliente_id">Estado del cliente <span class="text-danger">*</span></label>
                            <select id="estadoCliente_id" name="estadoCliente_id" class="form-control @error('estadoCliente_id') form-control-warning @enderror" required>
                                <option value="" {{ old('estadoCliente_id', $cliente->id_estado_cliente) ? '' : 'selected' }}>Seleccione un estado</option>
                                @foreach ($estadosClientes as $estadoCliente)
                                    <option value="{{ $estadoCliente->id }}" {{ old('estadoCliente_id', $cliente->id_estado_cliente) == $estadoCliente->id ? 'selected' : '' }}>
                                        {{ $estadoCliente->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seleccionar estado del cliente.</small>
                            @error('estadoCliente_id') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Contratos --}}
                        <div class="col-md-12 mb-3">
                            <label>Contrato <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap">
                                @php
                                    $seleccionados = old('tiposDeContratos', $cliente->tiposContrato->pluck('id')->toArray());
                                @endphp
                                @foreach ($tiposDeContratos as $tipoDeContrato)
                                    @php $cid = 'contrato_'.$tipoDeContrato->id; @endphp
                                    <div class="form-check d-flex align-items-center me-5 mb-2">
                                        <input
                                            type="checkbox"
                                            name="tiposDeContratos[]"
                                            value="{{ $tipoDeContrato->id }}"
                                            class="form-check-input"
                                            id="{{ $cid }}"
                                            {{ in_array($tipoDeContrato->id, $seleccionados) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label ps-2" for="{{ $cid }}">
                                            {{ $tipoDeContrato->nombre }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            @error('tiposDeContratos')
                                <div class="text-warning">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Director ejecutivo --}}
                        <div class="col-md-6 mb-3">
                            <label for="usuario_id">Director ejecutivo <span class="text-danger">*</span></label>
                            <select id="usuario_id" name="usuario_id" class="form-control @error('usuario_id') form-control-warning @enderror" required>
                                <option value="">Seleccione un ejecutivo</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" {{ old('usuario_id', $cliente->id_usuario) == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Seleccionar ejecutivo asignado.</small>
                            @error('usuario_id') <div class="text-warning">{{ $message }}</div> @enderror
                        </div>

                        {{-- Instagram --}}
                        <div class="col-md-6 mb-3">
                            <label for="url_instagram">Instagram</label>
                            <input type="url" id="url_instagram" name="url_instagram" class="form-control" placeholder="https://instagram.com/..." value="{{ old('url_instagram', $instagram ?? '') }}">
                        </div>

                        {{-- Facebook --}}
                        <div class="col-md-6 mb-3">
                            <label for="url_facebook">Facebook</label>
                            <input type="url" id="url_facebook" name="url_facebook" class="form-control" placeholder="https://facebook.com/..." value="{{ old('url_facebook', $facebook ?? '') }}">
                        </div>

                        {{-- YouTube --}}
                        <div class="col-md-6 mb-3">
                            <label for="url_youtube">YouTube</label>
                            <input type="url" id="url_youtube" name="url_youtube" class="form-control" placeholder="https://youtube.com/..." value="{{ old('url_youtube', $youtube ?? '') }}">
                        </div>

                        {{-- Botón --}}
                        <div class="col-md-12 mt-4">
                            <button type="submit" class="btn btn-success w-100">Actualizar cliente</button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Imagen decorativa --}}
            <div class="col-md-6 d-flex align-items-center justify-content-center" style="background-color: #003B7B;">
                <img src="{{ asset('img/Logo_Himalaya_blanco-10.png') }}" alt="Logo Himalaya" class="img-fluid" style="max-width: 90%; height: auto;">
            </div>
        </div>
    </x-slot>
</x-app-layout>
