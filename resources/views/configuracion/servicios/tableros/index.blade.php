<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('config.servicios.create', $cliente->id) }}" class="btn btn-primary me-2">Crear
            configuración</a>
        <a href="{{ route('clientes.index') }}" class="btn btn-primary me-2">Volver</a>
    </x-slot>

    <x-slot name="titulo">
        Configuración de servicios
    </x-slot>

    <x-slot name="slot">
    </x-slot>
    @section('alert')
        <script>

        </script>
    @endsection
</x-app-layout>