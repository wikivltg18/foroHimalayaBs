<x-app-layout>
  <x-slot name="titulo">Crear servicio — {{ $cliente->nombre }}</x-slot>

  @push('styles')
  <style>
    .svc-wrap{max-width:980px;margin:0 auto}
    .svc-card{background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.06);padding:28px 28px 24px}
    .svc-title{font-weight:700;color:#0b3b70;margin-bottom:12px}
    .svc-label{font-weight:600;color:#0b3b70;margin-bottom:6px}
    .svc-client{font-weight:700;color:#0b3b70}
    .svc-input,.svc-select{height:44px;border-radius:12px;border:1px solid #d9e3f0;background:#f7fbff;padding:10px 12px;outline:none}
    .svc-input:focus,.svc-select:focus{border-color:#7db6ff;box-shadow:0 0 0 3px rgba(61,132,255,.15)}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media (max-width: 768px){.grid-2{grid-template-columns:1fr}}
    .input-with-label{display:flex;gap:10px;align-items:center}
    .input-with-label .lbl{min-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:#0b3b70;font-weight:600}
    .radio-chip{display:flex;gap:18px;align-items:center;flex-wrap:wrap}
    .radio-chip .form-check{display:flex;gap:8px;align-items:center}
    .row-inline{display:grid;grid-template-columns:1fr 1fr;gap:20px}
    @media (max-width: 768px){.row-inline{grid-template-columns:1fr}}
    .fases-list{display:flex;flex-direction:column;gap:10px}
    .fase-pill{background:linear-gradient(90deg,#67b7ff,#1790ff);color:#fff;border-radius:12px;padding:10px 14px;font-weight:600}
    .btn-primary{background:linear-gradient(90deg,#00a6ff,#006dff);border:none;border-radius:12px;color:#fff;font-weight:700;padding:12px 18px}
    .btn-primary:hover{opacity:.95}
    .btn-ghost{background:linear-gradient(90deg,#48e1ff,#17a0ff);color:#fff;border:none;border-radius:12px;font-weight:700;padding:12px 18px}
    .btn-ghost:hover{opacity:.95}
    .btns{display:flex;gap:10px;align-items:center}
    .svc-muted{color:#7c8aa5}
  </style>
  @endpush

  <x-slot name="slot">
    <div id="ctx"
         data-cliente="{{ $cliente->id }}"
         data-modalidad-inicial="{{ $selectedModalidadId }}"
         data-tipo-inicial="{{ $selectedTipoId }}">
    </div>

    <div class="svc-wrap">
      <div class="svc-card">

        <div class="mb-3">
          <div class="svc-label">Cliente:</div>
          <div class="svc-client">{{ $cliente->nombre }}</div>
        </div>

        <form id="form-servicio" action="{{ route('config.servicios.store', $cliente->id) }}" method="POST">
          @csrf

        <div class="mb-3">
            <label class="svc-label" for="nombre_servicio">Nombre del servicio:</label>
            <input type="text" id="nombre_servicio" name="nombre_servicio" class="svc-input"
                value="{{ old('nombre_servicio') }}" placeholder="Servicio 2" required>
        </div>

          <div class="mb-2"><div class="svc-title">Mapa del cliente:</div></div>

          <div class="grid-2 mb-4">
            @foreach($areasCatalog as $area)
              <div class="input-with-label">
                <div class="lbl">{{ $area->nombre }}:</div>
                <input type="number" step="0.5" min="0" class="svc-input" name="mapa[{{ $area->id }}]"
                       value="{{ old('mapa.'.$area->id) }}" placeholder="">
              </div>
            @endforeach
          </div>

          <div class="row-inline mb-3">
            <div>
              <div class="svc-label">Modalidad del servicio:</div>
              <div id="modalidades-container" class="radio-chip">
                @foreach($modalidades as $m)
                  <label class="form-check">
                    <input type="radio" name="modalidad_id" value="{{ $m->id }}"
                           {{ (string)$m->id === (string)$selectedModalidadId ? 'checked' : '' }}>
                    <span>{{ $m->nombre }}</span>
                  </label>
                @endforeach
              </div>
            </div>

            <div>
              <label class="svc-label" for="tipo_servicio">Tipo de servicio:</label>
              <select id="tipo_servicio" name="tipo_servicio_id" class="svc-select" required>
                <option value="">Seleccione un tipo</option>
                @foreach($tipos as $t)
                  <option value="{{ $t->id }}" @selected((string)$t->id === (string)$selectedTipoId)>{{ $t->nombre }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="mb-2"><div class="svc-title">Fases del servicio:</div></div>
          <div id="fases-preview" class="fases-list"></div>

          <div class="btns mt-4">
            <button type="submit" class="btn-primary">Guardar</button>
            <a href="{{ route('config.servicios.index', $cliente->id) }}" class="btn-ghost">Cerrar</a>
          </div>
        </form>
      </div>
    </div>
  </x-slot>

  @push('scripts')
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script>
  $(function () {
    $.ajaxSetup({ headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')} });

    const $tipo  = $('#tipo_servicio');
    const $fPrev = $('#fases-preview');


    const ctx = (() => {
      const $ctx = $('#ctx');
      return {
        clienteId: $ctx.data('cliente'),
        modalidadInicial: String($ctx.data('modalidad-inicial') || ''),
        tipoInicial: String($ctx.data('tipo-inicial') || '')
      };
    })();

    const URLS = {
      tiposPorModalidad: @json(route('config.servicios.ajax.tipos', ['modalidad' => 'MOD_PLACE'])),
      fasesPorTipo:      @json(route('config.servicios.ajax.fases',  ['tipo'      => 'TIP_PLACE']))
    };

    const toList = (res) => Array.isArray(res) ? res : (res?.tipos ?? res?.fases ?? []);

    function paintFases(list){
      $fPrev.empty();
      if (!list.length){
        $fPrev.append('<div class="svc-muted">Este tipo no tiene fases configuradas.</div>');
        return;
      }
      list.forEach(f => $fPrev.append(`<div class="fase-pill">${f.nombre ?? '(sin nombre)'}</div>`));
    }

    function cargarFases(tipoId){
      if (!tipoId){ paintFases([]); return; }
      const url = URLS.fasesPorTipo.replace('TIP_PLACE', encodeURIComponent(tipoId));
      $.get(url).done(res => paintFases(toList(res)))
               .fail(() => $fPrev.html('<div class="svc-muted">No se pudieron cargar las fases.</div>'));
    }

    function setTipos(items, selected){
      $tipo.html('<option value="">Seleccione un tipo</option>');
      items.forEach(t => $tipo.append(`<option value="${t.id}" ${String(t.id)===String(selected)?'selected':''}>${t.nombre}</option>`));
    }

    function cargarTipos(modalidadId, selectedTipoId = ''){
      if (!modalidadId){ setTipos([], ''); paintFases([]); return; }
      const url = URLS.tiposPorModalidad.replace('MOD_PLACE', encodeURIComponent(modalidadId));
      $tipo.html('<option value="">Cargando...</option>');
      $.get(url)
        .done(res => {
          const list = toList(res);
          setTipos(list, selectedTipoId);
          let tipoId = selectedTipoId;
          if (!tipoId && list.length){
            tipoId = list[0].id;
            $tipo.val(String(tipoId));
          }
          cargarFases(tipoId);
        })
        .fail(() => {
          $tipo.html('<option value="">Error al cargar tipos</option>');
          paintFases([]);
        });
    }

    // Eventos
    $(document).on('change', 'input[name="modalidad_id"]', function(){
      cargarTipos($(this).val(), '');
    });

    $tipo.on('change', function(){
      cargarFases($(this).val());
    });

    // Carga inicial
    if (ctx.tipoInicial){
      cargarFases(ctx.tipoInicial);
    } else if (ctx.modalidadInicial){
      cargarTipos(ctx.modalidadInicial, '');
    }
  });
  </script>
  @endpush
</x-app-layout>
