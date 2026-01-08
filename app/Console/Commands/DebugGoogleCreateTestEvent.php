<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class DebugGoogleCreateTestEvent extends Command
{
    protected $signature = 'debug:google-create-test-event {--user=1}';
    protected $description = 'Llama a GoogleCalendarController@createTestEvent como usuario de prueba';

    public function handle()
    {
        $this->info('Running debug:google-create-test-event');
        $userId = (int) $this->option('user');
        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        auth()->setUser($user);
        try {
            $response = app()->call('App\\Http\\Controllers\\GoogleCalendarController@createTestEvent');
            Log::info('createTestEvent response type: ' . (is_object($response) ? get_class($response) : gettype($response)));
            $this->info('Done. Check logs.');
        } catch (\Exception $e) {
            Log::error('Exception running createTestEvent: ' . $e->getMessage(), ['exception' => $e]);
            $this->error('Exception occurred, check logs');
            return 1;
        }

        return 0;
    }
}
