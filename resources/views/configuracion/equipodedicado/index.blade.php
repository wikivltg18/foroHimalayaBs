<x-app-layout>
    <x-slot name="buttonPress">
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">Crear configuración</a>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary">Crear tablero</a>
    </x-slot>

    <x-slot name="titulo">
        Configuración de servicios
    </x-slot>

    <x-slot name="slot">
        <span>Actualmente no tenemos configuraciones creadas</span>
    </x-slot>
    @push('alert')
        <script>
        </script>
    @endpush
</x-app-layout>