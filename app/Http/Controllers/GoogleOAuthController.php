<?php

namespace App\Http\Controllers;

use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
use App\Models\UserGoogleAccount;

class GoogleOAuthController extends Controller
{
    private function client(): Google_Client
    {
        $c = new Google_Client();
        $c->setClientId(env('GOOGLE_CLIENT_ID'));
        $c->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $c->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $c->setAccessType('offline');
        $c->setPrompt('consent');
        $c->setScopes(['https://www.googleapis.com/auth/calendar', 'https://www.googleapis.com/auth/userinfo.email']);
        return $c;
    }

    public function redirect()
    {
        return redirect()->away($this->client()->createAuthUrl());
    }

    public function callback(Request $r)
    {
        $client = $this->client();
        if (!$r->code) return back()->withErrors('Error en OAuth');
        $token = $client->fetchAccessTokenWithAuthCode($r->code);
        if (isset($token['error'])) return back()->withErrors('Error en OAuth');

        $client->setAccessToken($token);
        $googleMe = (new \Google_Service_Oauth2($client))->userinfo->get();

        UserGoogleAccount::updateOrCreate(
            ['user_id' => auth()->id()],
            [
                'google_user_id' => $googleMe->id,
                'email'         => $googleMe->email,
                'access_token'  => json_encode($token),
                'refresh_token' => $token['refresh_token'] ?? null,
                'token_expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
            ]
        );

        return redirect()->route('profile.show')->with('success', 'Cuenta Google conectada.');
    }
}