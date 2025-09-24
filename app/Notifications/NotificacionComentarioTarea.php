<?php

namespace App\Notifications;

use App\Models\TareaServicio;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NotificacionComentarioTarea extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TareaServicio $tarea,
        public string $comentarioResumen,
        public ?int $autorComentarioId = null
    ) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        $tablero = $this->tarea->columna->tablero ?? null;

        return [
            'tipo'        => 'comentario_tarea',
            'tarea'       => $this->tarea->titulo,
            'cliente'     => $tablero->nombre_cliente ?? optional($tablero->cliente)->nombre,
            'resumen'     => $this->comentarioResumen,
            'actor_id'    => $this->autorComentarioId,
            'created_at'  => now()->toDateTimeString(),
            'url'         => route('configuracion.servicios.tableros.show', [
                'cliente'  => $tablero->cliente_id ?? '',
                'servicio' => $tablero->servicio_id ?? '',
                'tablero'  => $tablero->id ?? ''
            ]) . '#comentarios',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $d = $this->toDatabase($notifiable);

        return (new MailMessage)
            ->subject('Nuevo comentario en tu tarea')
            ->greeting('Hola ' . ($notifiable->name ?? ''))
            ->line('Hay un nuevo comentario en la tarea:')
            ->line('Tarea: ' . ($d['tarea'] ?? ''))
            ->line('Cliente: ' . ($d['cliente'] ?? ''))
            ->line('Comentario: ' . ($d['resumen'] ?? ''))
            ->action('Ver comentarios', $d['url'] ?? url('/'));
    }
}