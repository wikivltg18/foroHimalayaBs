@props(['align' => 'end', 'width' => '']) {{-- Bootstrap usa "start" o "end" --}}

<div class="dropdown">
    <div class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        {{ $trigger }}
    </div>

    <ul class="dropdown-menu dropdown-menu-{{ $align }} {{ $width }}">
        {{ $content }}
    </ul>
</div>
