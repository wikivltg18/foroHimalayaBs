<x-guest-layout>
<div class="col-sm-12 col-md-6 order-sm-2 order-md-1" style="height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <form method="POST" action="{{ route('password.email') }}" class="px-5">
        @csrf
        <div class="px-3">
            <div class="mb-4 px-3 text-start">
                <div class="d-flex flex-column align-items-start mb-4">
                    <img src="{{ asset('img/logo_himalaya_Blue.png') }}" alt="Login" class="mb-4 rounded img-fluid">
                </div>
                <div class="mb-4">
                    <h1 class="text-white font-weight-bold" style="font-weight: 600">Las contraseñas se olvidan, las grandes ideas no.</h1>
                    <h5 class="text-white">Restablécela aquí.</h5>
                </div>
            </div>
            <!-- Email Address -->
            <div class="mb-3 px-3">
                <input type="email" id="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="Mail" required autofocus>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <!-- Submit Button -->
            <div class="d-flex px-3 justify-content-between align-items-center">
                <button type="submit" class="btn btn-primary" style="background-color: #00BDF8; border-color: #00BDF8; padding:0.4rem 4rem 0.4rem 4rem; font-weight: 700; color: white !important;">
                    {{ __('Enviar') }}
                </button>
                <a class="btn text-decoration-none small text-muted" href="{{ route('login') }}" style="background-color: #A8A8A8; border-color: #A8A8A8; padding:0.4rem 4rem 0.4rem 4rem; font-weight: 700; color: white !important;">
                    {{ __('Volver') }}
                </a>
            </div>
                <!-- Session Status -->
                @if (session('status'))
                <div class="pt-3">
                    <div class="mb-4 alert alert-success" style="background-color: #B4FFBC; color: black;">
                        {{ session('status') }}
                    </div>
                </div>
                @endif
        </div>
    </form>
</div>
<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 order-sm-1 order-md-2 d-none d-md-block pt-3" style="height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <a href="/">
        <img src="{{ asset('/img/Login.svg') }}" alt="Login" class="img-fluid mx-auto d-block" style="max-width: 80%; height: auto;">
    </a>
</div>
</x-guest-layout>
