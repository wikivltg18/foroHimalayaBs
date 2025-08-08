<x-app-layout>
<x-slot name="slot">
    <div class="row">
        <div class="col-md-12" style="height: 510px; overflow: hidden;">
            <img src="{{ asset('img/Principal.png') }}" alt="" class="img-fluid" style="width: 100%; height: auto;">
        </div>
    </div>
</x-slot>
@section('alert')
    @if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                title: 'Acceso denegado',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonText: 'Entendido'
            });
        });
    </script>
    @endif
@endsection
</x-app-layout>