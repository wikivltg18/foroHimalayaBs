<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\UserGoogleAccount;

class GoogleOAuthController extends Controller
{
    private function client(): \Google_Client
{
    $c = new \Google_Client();
    $c->setClientId(config('services.google.client_id'));
    $c->setClientSecret(config('services.google.client_secret'));
    $c->setRedirectUri(config('services.google.redirect_uri'));
    $c->setAccessType('offline'); // para refresh_token
    $c->setIncludeGrantedScopes(true);
    $c->setPrompt('consent select_account'); // fuerza consentimiento y selección de cuenta
    $c->setScopes([
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/userinfo.email',
    ]);
    return $c;
}

public function redirect()
{
    $client = $this->client();

    // Asegurar que el cliente use el valor actual de .env (evita inconsistencias con variables de entorno del servidor)
    $force = env('GOOGLE_FORCE_REDIRECT_URI');
    if ($force) {
        $client->setRedirectUri($force);
        Log::info('Forcing GOOGLE_FORCE_REDIRECT_URI: ' . $force);
    } else {
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        Log::info('Forcing GOOGLE_REDIRECT_URI: ' . config('services.google.redirect_uri'));
    }

    $authUrl = $client->createAuthUrl();

    // Registrar la URL de autorización para verificar el redirect_uri que genera la app
    Log::info('Google OAuth URL: ' . $authUrl);
    Log::info('Using GOOGLE_REDIRECT_URI: ' . config('services.google.redirect_uri'));

    return redirect($authUrl);
}

public function callback(Request $r)
{
    $client = $this->client();
    if (!$r->code) return back()->withErrors('Error en OAuth');

    $token = $client->fetchAccessTokenWithAuthCode($r->code);
    if (isset($token['error'])) return back()->withErrors('Error en OAuth');

    $client->setAccessToken($token);
    $googleMe = (new \Google_Service_Oauth2($client))->userinfo->get();

    // Preservar refresh_token previo si no viene en esta respuesta
    $existing = \App\Models\UserGoogleAccount::where('user_id', auth()->id())->first();

    \App\Models\UserGoogleAccount::updateOrCreate(
        ['user_id' => auth()->id()],
        [
            'google_user_id'    => $googleMe->id,
            'email'             => $googleMe->email,
            'access_token'      => json_encode($client->getAccessToken()),
            'refresh_token'     => $token['refresh_token'] ?? ($existing->refresh_token ?? null),
            'token_expires_at'  => now()->addSeconds(($token['expires_in'] ?? 3600)),
        ]
    );

    return redirect()->route('google.calendars')->with('success', 'Cuenta Google conectada. Selecciona tu calendario por defecto.');
}

}