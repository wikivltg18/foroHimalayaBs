<?php

namespace App\Jobs;

use App\Models\TareaBloque;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemoveTaskBlocksFromCalendarJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $tareaId) {}

    public function handle(): void
    {
        $rows = TareaBloque::where('tarea_id', $this->tareaId)->pluck('id');
        foreach ($rows as $blockId) {
            dispatch(new \App\Jobs\RemoveSingleBlockFromCalendarJob($blockId))->onQueue('calendar');
        }
    }
}