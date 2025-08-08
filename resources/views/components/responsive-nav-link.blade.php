@props(['active'])

@php
$classes = 'nav-link w-100 text-start';
if ($active ?? false) {
    $classes .= ' active';
}
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
