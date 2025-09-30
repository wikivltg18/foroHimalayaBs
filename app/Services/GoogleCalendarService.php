<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use App\Models\UserGoogleAccount;

class GoogleCalendarService
{
    private function client(UserGoogleAccount $acc): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline');
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);
        $token = json_decode($acc->access_token, true);
        $client->setAccessToken($token);
        if ($client->isAccessTokenExpired() && $acc->refresh_token) {
            $client->fetchAccessTokenWithRefreshToken($acc->refresh_token);
            $new = $client->getAccessToken();
            $acc->update(['access_token' => json_encode($new), 'token_expires_at' => now()->addSeconds($new['expires_in'] ?? 3600)]);
        }
        return $client;
    }

    public function createEvent(UserGoogleAccount $acc, array $payload): string
    {
        $client = $this->client($acc);
        $service = new Google_Service_Calendar($client);

        $event = new Google_Service_Calendar_Event([
            'summary'     => $payload['summary'],
            'description' => $payload['description'] ?? null,
            'start'       => ['dateTime' => $payload['start']->toRfc3339String(), 'timeZone' => config('app.timezone')],
            'end'         => ['dateTime' => $payload['end']->toRfc3339String(),   'timeZone' => config('app.timezone')],
            'reminders'   => ['useDefault' => false, 'overrides' => [['method' => 'email', 'minutes' => 1440], ['method' => 'popup', 'minutes' => 60]]],
        ]);

        $calendarId = $acc->calendar_id ?: 'primary';
        $created = $service->events->insert($calendarId, $event);
        return $created->getId();
    }
}