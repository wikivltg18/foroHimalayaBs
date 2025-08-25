<x-guest-layout>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<div class="col-sm-12 col-md-6 order-sm-2 order-md-1" style="height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <!-- Session Status -->
    @if (session('status'))
        <div class="mb-4 alert alert-success">
            {{ session('status') }}
        </div>
    @endif
    <form method="POST" action="{{ route('login') }}">
        @csrf
    <div class="px-5">
        <div class="mb-4 text-start">
            <div class="d-flex flex-column align-items-start mb-4">
                <img src="{{ asset('img/logo_himalaya_Blue.png') }}" alt="Login" class="mb-4 rounded img-fluid">
            </div>
            <div class="mb-4">
                <h1 class="text-white font-weight-bold" style="font-weight: 700">Bienvenido</h1>
                <h5 class="text-white">Accede a tu espacio de trabajo y colabora con tu equipo.</h5>
            </div>
        </div>
        <!-- Email Address -->
        <div class="mb-3">
            <input type="email" class="form-control" id="email" name="email" placeholder="Mail" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <div class="mt-1 text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <!-- Password -->
        <div class="mb-3 position-relative">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required autocomplete="current-password">
            <button type="button" class="btn position-absolute end-0 top-50 translate-middle-y" onclick="togglePassword()" style="background: none; border: none;">
                <i class="fas fa-eye" id="togglePassword"></i>
            </button>
            @error('password')
                <div class="mt-1 text-danger small">{{ $message }}</div>
            @enderror
        </div>
        
        <!-- Actions -->
        <div class="d-flex justify-content-between align-items-center">
        <!-- Remember Me -->
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                <label class="form-check-label text-white" for="remember_me">{{ __('Recuerdame') }}</label>
            </div>
            @if (Route::has('password.request'))
                <a class="text-decoration-none small text-muted" href="{{ route('password.request') }}" style="font-weight: 700; color: white !important;">
                    {{ __('¿Olvidaste la clave?') }}
                </a>
            @endif
            </div>
            <button type="submit" class="btn btn-primary w-100" style="background-color: #00BDF8; border-color: #00BDF8; font-weight: 700;">
                {{ __('Iniciar sesión') }}
            </button>
        </div>
    </form>
</div>
<div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 order-sm-1 order-md-2 d-none d-md-block pt-3" style="height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center;">
    <a href="/">
        <img src="{{ asset('/img/Login.svg') }}" alt="Login" class="img-fluid mx-auto d-block" style="max-width: 80%; height: auto;">
    </a>
</div>
@push('scripts')
            <script>
            function togglePassword() {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.getElementById('togglePassword');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    toggleIcon.classList.remove('fa-eye');
                    toggleIcon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                }
            }
        </script>
@endpush
</x-guest-layout>
