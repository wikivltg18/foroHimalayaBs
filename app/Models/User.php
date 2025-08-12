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

    // Definir los campos que se pueden asignar masivamente
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

    // Relación de pertenencia a Cargo, Area y Role
    /**
     * Obtener el cargo asociado al usuario.
     */
    public function cargo(): BelongsTo
    
    {
        // Definir la relación de pertenencia a Cargo
        // 'id_cargo' es la FK en users que apunta a cargos
        // 'id' es la local key en cargos
        // Retorna una instancia de la relación BelongsTo
        // Usando el modelo Cargo
        // Esto permite acceder al cargo asociado a este usuario
        return $this->belongsTo(Cargo::class, 'id_cargo');
    }

    // Relación de pertenencia a Area
    /**
     * Obtener el área asociada al usuario.
     */
    public function area(): BelongsTo
    
    {
        // Definir la relación de pertenencia a Area
        // 'id_area' es la FK en users que apunta a areas
        // 'id' es la local key en areas
        // Retorna una instancia de la relación BelongsTo
        // Usando el modelo Area
        // Esto permite acceder al área asociada a este usuario
        return $this->belongsTo(Area::class, 'id_area');
    }

    // Relación de pertenencia a Role
    /**
     * Obtener el rol asociado al usuario.
     */
    public function role(): BelongsTo
    
    {
        // Definir la relación de pertenencia a Role
        // 'id_rol' es la FK en users que apunta a roles
        // 'id' es la local key en roles
        // Retorna una instancia de la relación BelongsTo
        // Usando el modelo Role
        // Esto permite acceder al rol asociado a este usuario
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