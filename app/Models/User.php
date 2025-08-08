<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Area;
use App\Models\Cargo;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'foto_perfil',
        'name',
        'email',
        'password',
        'telefono',
        'f_nacimiento',
        'h_defecto',
        'id_cargo',
        'id_area',
        'id_rol',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'f_nacimiento' => 'date',
        ];
    }
    public function cargo(): BelongsTo
    
    {
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }

    public function area(): BelongsTo
    
    {
        return $this->belongsTo(Area::class, 'id_area');
    }

    public function role(): BelongsTo
    
    {
        return $this->belongsTo(Role::class, 'id_rol');
    }

    
    //Permite que el administrador tenga el acceso al sistema siempre
    
/*
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if ($this->hasRole('superadministrador')) {
            return true;
        }
        return parent::hasPermissionTo($permission, $guardName);
    }*/
}