<?php

namespace App\Notifications;

use App\Models\TareaServicio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NotificacionAsignacionTarea extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TareaServicio $tarea) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        $tablero = $this->tarea->columna->tablero ?? null;
        return [
            'tipo' => 'tarea_asignada',
            'cliente' => $tablero->nombre_cliente ?? optional($tablero->cliente)->nombre,
            'tarea'   => $this->tarea->titulo,
            'fecha'   => optional($this->tarea->fecha_de_entrega)?->toDateTimeString(),
            'area'    => optional($this->tarea->area)->nombre,
            'url'     => route('configuracion.servicios.tableros.show', [
                'cliente' => $tablero->cliente_id ?? '',
                'servicio' => $tablero->servicio_id ?? '',
                
                'tablero' => $tablero->id ?? ''
            ]),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $d = $this->toDatabase($notifiable);
        return (new MailMessage)
            ->subject('Nueva tarea asignada')
            ->greeting('Hola ' . $notifiable->name)
            ->line('Cliente: ' . $d['cliente'])
            ->line('Tarea: ' . $d['tarea'])
            ->line('Fecha de entrega: ' . ($d['fecha'] ? \Carbon\Carbon::parse($d['fecha'])->format('d/m/Y H:i') : '—'))
            ->line('Área: ' . $d['area'])
            ->action('Abrir tablero', $d['url']);
    }
}