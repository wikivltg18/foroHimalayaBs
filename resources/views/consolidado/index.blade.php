<x-app-layout>
    <x-slot name="titulo">
        Consolidado de Horas - {{ $servicio->nombre_servicio }}
    </x-slot>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/litepicker/css/litepicker.css') }}" />
    <style>
        .dashboard {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
        }

        /* HEADER */
        .header-consolidado {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
            border-bottom: 1px solid #eee;
            padding-bottom: 16px;
        }

        .client-info small {
            color: #777;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .client-info strong {
            display: block;
            font-size: 16px;
            color: #003b82;
        }

        /* FILTROS */
        .filters-consolidado {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        /* METRICAS */
        .metrics-consolidado {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }

        .metric-card {
            padding: 15px;
            border-radius: 8px;
            color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .metric-card small {
            opacity: 0.9;
            font-size: 11px;
            display: block;
            margin-bottom: 5px;
        }

        .metric-card strong {
            display: flex;
            font-size: 24px;
            justify-content: center;
            align-items: center;
        }

        .bg-green { background: #2e7d32; }
        .bg-cyan  { background: #00bcd4; }
        .bg-orange{ background: #f57c00; }
        .bg-blue  { background: #1976d2; }

        /* TABLA */
        .table-consolidado {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .table-consolidado thead {
            background: #003b82;
            color: #fff;
        }

        .table-consolidado th, .table-consolidado td {
            padding: 12px 15px;
            text-align: center;
        }

        .table-consolidado tbody tr:nth-child(even) {
            background: #f2f7fc;
        }

        .progress {
            height: 10px;
            border-radius: 5px;
            background-color: #e9ecef;
        }

        .progress-bar {
            border-radius: 5px;
        }

        /* Estilos litepicker */
        #litepicker-input {
            cursor: pointer;
            background-color: white !important;
        }
    </style>
    @endpush

    <div class="dashboard">
        <!-- HEADER -->
        <div class="header-consolidado">
            <div class="client-info">
                <small>Cliente</small>
                <strong>{{ $servicio->cliente->nombre }}</strong>
            </div>
            <div class="client-info">
                <small>Servicio</small>
                <strong>{{ $servicio->nombre_servicio }}</strong>
            </div>
            <div class="client-info">
                <small>Periodo</small>
                <strong>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</strong>
            </div>
        </div>

        <!-- FILTROS -->
        <div class="filters-consolidado">
            <form action="{{ route('servicios.consolidado.index', $servicio) }}" method="GET" id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Rango de Fechas</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3"></i></span>
                            <input type="text" id="litepicker-input" class="form-control form-control-sm border-start-0" placeholder="Seleccione fechas..." readonly>
                        </div>
                        <input type="hidden" name="start_date" id="start_date" value="{{ $startDate }}">
                        <input type="hidden" name="end_date" id="end_date" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Área</label>
                        <select name="area_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todas las áreas</option>
                            @foreach($areasFiltro as $area)
                                <option value="{{ $area->id }}" {{ $selectedAreaId == $area->id ? 'selected' : '' }}>
                                    {{ $area->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Colaborador</label>
                        <select name="usuario_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Todos los colaboradores</option>
                            @foreach($usuariosFiltro as $usuario)
                                <option value="{{ $usuario->id }}" {{ $selectedUsuarioId == $usuario->id ? 'selected' : '' }}>
                                    {{ $usuario->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-search"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- METRICAS -->
        <div class="metrics-consolidado">
            <div class="metric-card bg-green">
                <small>Contratadas</small>
                <strong>{{ number_format($totalContratadas, 1) }}</strong>
            </div>
            <div class="metric-card bg-cyan">
                <small>Utilizadas</small>
                <strong>{{ number_format($totalConsumidas, 1) }}</strong>
            </div>
            <div class="metric-card bg-orange">
                <small>Restantes</small>
                <strong>{{ number_format($totalRestantes, 1) }}</strong>
            </div>
            <div class="metric-card bg-blue">
                <small>% Ejecución</small>
                <strong>{{ number_format($totalPorcentaje, 2) }}%</strong>
            </div>
        </div>

        <!-- TABLA -->
        <div class="table-responsive">
            <table class="table-consolidado">
                <thead>
                    <tr>
                        <th class="text-start">Área</th>
                        <th>Contratadas</th>
                        <th>Consumidas</th>
                        <th>Restantes</th>
                        <th>Progreso</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dataareas as $item)
                    <tr>
                        <td class="text-start fw-bold">{{ $item['area']->nombre }}</td>
                        <td>{{ number_format($item['contratadas'], 1) }}</td>
                        <td>{{ number_format($item['consumidas'], 1) }}</td>
                        <td>{{ number_format($item['restantes'], 1) }}</td>
                        <td style="width: 200px;">
                            <div class="progress">
                                <div class="progress-bar {{ $item['porcentaje'] > 100 ? 'bg-danger' : ($item['porcentaje'] > 80 ? 'bg-warning' : 'bg-success') }}" 
                                     role="progressbar" 
                                     style="width: {{ min(100, $item['porcentaje']) }}%" 
                                     aria-valuenow="{{ $item['porcentaje'] }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100"></div>
                            </div>
                        </td>
                        <td class="fw-bold {{ $item['porcentaje'] > 100 ? 'text-danger' : '' }}">
                            {{ $item['porcentaje'] }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No hay áreas contratadas asignadas a este servicio.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script src="{{ asset('vendor/litepicker/js/litepicker.js') }}"></script>
    <script src="{{ asset('vendor/litepicker/plugins/ranges.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const picker = new Litepicker({
                element: document.getElementById('litepicker-input'),
                plugins: ['ranges'],
                singleMode: false,
                format: 'YYYY-MM-DD',
                startDate: '{{ $startDate }}',
                endDate: '{{ $endDate }}',
                lang: 'es-ES',
                numberOfMonths: 2,
                numberOfColumns: 2,
                autoApply: true,
                ranges: {
                    'Hoy': [new Date(), new Date()],
                    'Ayer': [
                        new Date(new Date().setDate(new Date().getDate() - 1)),
                        new Date(new Date().setDate(new Date().getDate() - 1))
                    ],
                    'Última semana': [
                        new Date(new Date().setDate(new Date().getDate() - 6)),
                        new Date()
                    ],
                    'Últimos 30 Días': [
                        new Date(new Date().setDate(new Date().getDate() - 29)),
                        new Date()
                    ],
                    'Este Mes': [
                        new Date(new Date().getFullYear(), new Date().getMonth(), 1),
                        new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0)
                    ],
                    'Mes Pasado': [
                        new Date(new Date().getFullYear(), new Date().getMonth() - 1, 1),
                        new Date(new Date().getFullYear(), new Date().getMonth(), 0)
                    ],
                    'Últimos 3 Meses': [
                        new Date(new Date().getFullYear(), new Date().getMonth() - 3, 1),
                        new Date()
                    ],
                    'Últimos 6 Meses': [
                        new Date(new Date().getFullYear(), new Date().getMonth() - 6, 1),
                        new Date()
                    ],
                    'Últimos 12 Meses': [
                        new Date(new Date().getFullYear(), new Date().getMonth() - 12, 1),
                        new Date()
                    ]
                },
                setup: (picker) => {
                    picker.on('selected', (date1, date2) => {
                        document.getElementById('start_date').value = date1.format('YYYY-MM-DD');
                        document.getElementById('end_date').value = date2.format('YYYY-MM-DD');
                        document.getElementById('filterForm').submit();
                    });
                }
            });
            
            // Inicializar texto del input
            const startStr = picker.startDate.format('YYYY-MM-DD');
            const endStr = picker.endDate.format('YYYY-MM-DD');
            document.getElementById('litepicker-input').value = `${startStr} - ${endStr}`;
        });
    </script>
    @endpush
</x-app-layout>
