<x-app-layout>
    <x-slot name="titulo">Notificaciones</x-slot>

    <div class="container py-4">
        <h1 class="mb-4">Notificaciones</h1>

        <h5 class="mb-3">No leídas ({{ $unread->count() }})</h5>
        <ul class="list-group mb-4">
            @forelse($unread as $n)
                @php $d = $n->data ?? []; @endphp
                <li class="list-group-item d-flex justify-content-between align-items-start">
                    <div class="me-3">
                        <div class="fw-semibold">{{ $d['tarea'] ?? 'Tarea' }}</div>
                        <div class="small text-muted">
                            {{ $d['cliente'] ?? '' }}
                            @if(!empty($d['fecha']))
                                — vence: {{ \Carbon\Carbon::parse($d['fecha'])->format('d/m/Y H:i') }}
                            @endif
                        </div>
                        @if(!empty($d['url']))
                            <a class="btn btn-sm btn-outline-primary mt-2" href="{{ $d['url'] }}">Abrir</a>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('notificaciones.read', $n->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">Marcar leída</button>
                    </form>
                </li>
            @empty
                <li class="list-group-item text-muted">No tienes notificaciones no leídas.</li>
            @endforelse
        </ul>

        <h5 class="mb-3">Todas</h5>
        <ul class="list-group mb-3">
            @foreach($all as $n)
                @php $d = $n->data ?? []; @endphp
                <li
                    class="list-group-item d-flex justify-content-between align-items-start {{ $n->read_at ? '' : 'bg-light' }}">
                    <div class="me-3">
                        <div class="{{ $n->read_at ? 'text-muted' : 'fw-semibold' }}">
                            {{ $d['tarea'] ?? 'Tarea' }} — {{ $d['cliente'] ?? '' }}
                        </div>
                        <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                    </div>
                    @if(!$n->read_at)
                        <form method="POST" action="{{ route('notificaciones.read', $n->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-secondary">Marcar leída</button>
                        </form>
                    @endif
                </li>
            @endforeach
        </ul>

        {{ $all->links() }}
    </div>
</x-app-layout>