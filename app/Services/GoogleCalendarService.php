<?php 

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use App\Models\UserGoogleAccount;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private function client(UserGoogleAccount $acc): Google_Client
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (!$clientId || !$clientSecret) {
            throw new \Exception('Google Client ID/Secret not defined in .env or config');
        }

        $client = new Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri(config('services.google.redirect_uri'));
        $client->setAccessType('offline');
        $client->setScopes(['https://www.googleapis.com/auth/calendar']);

        $token = json_decode($acc->access_token, true);
        if (!$token) {
            \Log::error("Invalid access_token for UserGoogleAccount ID: {$acc->id}. Raw data: " . substr($acc->access_token, 0, 20));
            // Instead of crashing immediately here with "json key missing", let's throw a clearer exception
            throw new \Exception('Invalid Access Token stored for user.');
        }

        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired() && $acc->refresh_token) {
            $client->fetchAccessTokenWithRefreshToken($acc->refresh_token);
            $new = $client->getAccessToken();
            
            if (!$new) {
                 \Log::error("Failed to refresh token for UserGoogleAccount ID: {$acc->id}");
                 throw new \Exception('Failed to refresh Google Access Token.');
            }

            $acc->update([
                'access_token'     => json_encode($new),
                'token_expires_at' => now()->addSeconds($new['expires_in'] ?? 3600),
            ]);
        }

        return $client;
    }

    private function service(UserGoogleAccount $acc): Google_Service_Calendar
    {
        return new Google_Service_Calendar($this->client($acc));
    }

    public function listCalendars(UserGoogleAccount $acc): array
    {
        $service = $this->service($acc);
        $calendarList = $service->calendarList->listCalendarList();
        
        return collect($calendarList->getItems())->map(function($cal) {
            return [
                'id' => $cal->getId(),
                'summary' => $cal->getSummary(),
                'description' => $cal->getDescription(),
                'primary' => $cal->getPrimary() ?? false,
                'accessRole' => $cal->getAccessRole(),
            ];
        })->toArray();
    }

public function createEvent(UserGoogleAccount $acc, array $payload, ?string $calendarId = null): string
{
    $service = $this->service($acc);

    $event = new Google_Service_Calendar_Event([
        'summary'     => $payload['summary'],
        'description' => $payload['description'] ?? null,
        'start'       => ['dateTime' => $payload['start']->toRfc3339String(), 'timeZone' => config('app.timezone')],
        'end'         => ['dateTime' => $payload['end']->toRfc3339String(),   'timeZone' => config('app.timezone')],
        // 👇 soporte opcional de asistentes (organizer = dueño del calendario acc)
        'attendees'   => $payload['attendees'] ?? [], // [['email'=>'...'], ...] - usar array vacío en vez de null para evitar foreach() sobre null
        'reminders'   => ['useDefault' => false, 'overrides' => [['method'=>'email','minutes'=>1440], ['method'=>'popup','minutes'=>60]]],
    ]);

    $calendarId = $calendarId ?? ($acc->calendar_id ?: 'primary');
    $created = $service->events->insert($calendarId, $event, ['sendUpdates' => 'none']);
    return $created->getId();
}

public function updateEvent(UserGoogleAccount $acc, string $eventId, array $payload, ?string $calendarId = null): void
{
    $service = $this->service($acc);
    $calendarId = $calendarId ?? ($acc->calendar_id ?: 'primary');
    $event = $service->events->get($calendarId, $eventId);

    if (array_key_exists('summary', $payload))     $event->setSummary($payload['summary']);
    if (array_key_exists('description', $payload)) $event->setDescription($payload['description']);
    if (array_key_exists('attendees', $payload))   $event->setAttendees($payload['attendees']);

    if (isset($payload['start'], $payload['end'])) {
        $event->setStart(['dateTime' => $payload['start']->toRfc3339String(), 'timeZone' => config('app.timezone')]);
        $event->setEnd(['dateTime'   => $payload['end']->toRfc3339String(),   'timeZone' => config('app.timezone')]);
    }

    $service->events->update($calendarId, $eventId, $event, ['sendUpdates' => 'none']);
}

public function deleteEvent(UserGoogleAccount $acc, string $eventId, ?string $calendarId = null): void
{
    $service = $this->service($acc);
    $calendarId = $calendarId ?? ($acc->calendar_id ?: 'primary');
    $service->events->delete($calendarId, $eventId);
}
}