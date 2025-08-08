<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Información del perfil') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Actualice la información del perfil y la dirección de correo electrónico de su cuenta.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')
        <div class="row">
                @if ($user->foto_perfil)
                <div class="col-md-4">
                    <div class="text-center mb-4">
                        <img src="{{ asset('storage/' . $user->foto_perfil) }}" alt="Foto actual" class="rounded img-fluid d-block mx-auto" style="width: 300px; height: 300px; object-fit: cover;">
                    </div>
                </div>
                @endif
                <div class="col-md-8">
                <div>
                    <x-input-label for="foto_perfil" :value="__('Foto de perfil')" />
                    <x-text-input id="foto_perfil" name="foto_perfil" type="file" class="mt-1 block w-full mb-3" accept="image/*"/>
                    <x-input-error class="mt-2" :messages="$errors->get('foto_perfil')" />
                </div>

                <div>
                    <x-input-label for="name" :value="__('Nombre')" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full mb-3" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Correo electrónico')" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full mb-3" :value="old('email', $user->email)" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div>
                            <p class="text-sm mt-2 text-gray-800">
                                {{ __('Su dirección de correo electrónico no está verificada.') }}

                                <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('Haga clic aquí para volver a enviar el correo electrónico de verificación.') }}
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 font-medium text-sm text-green-600">
                                    {{ __('Se ha enviado un nuevo enlace de verificación a su dirección de correo electrónico.') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            <div class="flex items-center gap-4">
                <x-primary-button>{{ __('Guardar') }}</x-primary-button>
            </div>
            </div>
        </div>
    </form>
@section('alert')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('status') === 'profile-updated')
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Información actualizada.',
                    icon: 'success',
                    confirmButtonText: 'Ok'
                });
            @endif
        });
    </script>
@endsection
</section>
