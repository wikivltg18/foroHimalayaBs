<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGoogleAccount extends Model
{
    protected $fillable = ['user_id', 'google_user_id', 'email', 'access_token', 'refresh_token', 'calendar_id', 'token_expires_at'];
    protected $casts = ['token_expires_at' => 'datetime'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}