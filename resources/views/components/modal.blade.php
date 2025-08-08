@props([
    'name',
    'show' => false,
    'maxWidth' => 'lg' // Puedes usar sm, md, lg, xl
])

@php
$sizeClass = match ($maxWidth) {
    'sm' => 'modal-sm',
    'md' => '', // default
    'lg' => 'modal-lg',
    'xl' => 'modal-xl',
    '2xl' => 'modal-xl', // Bootstrap no tiene 2xl, puedes usar xl
    default => '',
};
@endphp

<!-- Modal -->
<div class="modal fade {{ $show ? 'show d-block' : '' }}" id="{{ $name }}" tabindex="-1" aria-hidden="{{ $show ? 'false' : 'true' }}" style="{{ $show ? 'display: block;' : '' }}">
    <div class="modal-dialog {{ $sizeClass }}">
        <div class="modal-content">
            {{ $slot }}
        </div>
    </div>
</div>

@if ($show)
<!-- Backdrop -->
<div class="modal-backdrop fade show"></div>
@endif
