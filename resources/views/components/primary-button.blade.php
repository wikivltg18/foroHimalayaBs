<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-primary text-uppercase fw-semibold']) }}>
    {{ $slot }}
</button>
