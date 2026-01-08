<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DebugGoogleRedirect extends Command
{
    protected $signature = 'debug:google-redirect';
    protected $description = 'Llama a GoogleOAuthController@redirect para registrar la URL de autorización en logs';

    public function handle()
    {
        Log::info('Running debug:google-redirect command');
        file_put_contents(storage_path('logs/debug-google-redirect.log'), "Running debug command at " . now() . "\n", FILE_APPEND);
        $response = app()->call('\App\Http\Controllers\GoogleOAuthController@redirect');
        $line = 'Controller redirect returned: ' . (is_object($response) ? get_class($response) : gettype($response));
        Log::info($line);
        file_put_contents(storage_path('logs/debug-google-redirect.log'), $line . "\n", FILE_APPEND);
        $this->info('Done. Check storage/logs/laravel.log and storage/logs/debug-google-redirect.log for entries.');
    }
}
