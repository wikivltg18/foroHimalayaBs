<?php

namespace App\Notifications;

use App\Models\TareaServicio;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NotificacionTareaFinalizada extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TareaServicio $tarea, public ?int $finalizadaPorId = null) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        $tablero = $this->tarea->columna->tablero ?? null;

        return [
            'tipo'     => 'tarea_finalizada',
            'tarea'    => $this->tarea->titulo,
            'cliente'  => $tablero->nombre_cliente ?? optional($tablero->cliente)->nombre,
            'area'     => optional($this->tarea->area)->nombre,
            'fecha'    => optional($this->tarea->finalizada_at)?->toDateTimeString(),
            'actor_id' => $this->finalizadaPorId,
            'url'      => route('configuracion.servicios.tableros.show', [
                'cliente'  => $tablero->cliente_id ?? '',
                'servicio' => $tablero->servicio_id ?? '',
                'tablero'  => $tablero->id ?? ''
            ]),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $d = $this->toDatabase($notifiable);

        return (new MailMessage)
            ->subject('Tarea finalizada')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('La tarea fue finalizada:')
            ->line('Tarea: ' . ($d['tarea'] ?? ''))
            ->line('Cliente: ' . ($d['cliente'] ?? ''))
            ->line('Área: ' . ($d['area'] ?? ''))
            ->line('Fecha: ' . (!empty($d['fecha']) ? dtz($d['fecha'], 'd/m/Y H:i') : '—'))
            ->action('Ver tablero', $d['url'] ?? url('/'));
    }
}