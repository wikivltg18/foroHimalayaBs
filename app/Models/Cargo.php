<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    
protected $fillable = ['nombre', 'descripcion'];

public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_cargo');
    }

}
