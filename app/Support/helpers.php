<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

if (! function_exists('dtz')) {
    /**
     * Renderiza una fecha/hora en la tz de display (por defecto America/Bogota).
     *
     * @param  \DateTimeInterface|string|null  $date
     * @param  string                          $format
     * @param  string|null                     $tz
     * @return string
     */
    function dtz($date, string $format = 'd/m/Y H:i', ?string $tz = null): string
    {
        if (! $date) return '';

        // Normaliza a instancia de Carbon (acepta DateTimeInterface o string)
        $c = $date instanceof Carbon
            ? $date
            : Carbon::parse($date); // parse en tz app (UTC), luego convertimos

        $displayTz = $tz ?: (Config::get('app.display_timezone') ?: 'America/Bogota');

        return $c->copy()->timezone($displayTz)->format($format);
    }
}