<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Str;
use App\Models\EstadoCliente;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directores = User::where('id_cargo', 6)
            ->orWhereHas('cargo', function ($q) {
                $q->where('nombre', 'Director Ejecutivo');
            })->get();

        $estadoDefault = EstadoCliente::inRandomOrder()->first();

        foreach ($directores as $usuario) {
            Cliente::create([
                'logo' => 'logos/' . Str::random(10) . '.png',
                'nombre' => 'Empresa ' . Str::random(5),
                'correo_electronico' => Str::lower(Str::random(5)) . '@example.com',
                'telefono' => '+57' . rand(3100000000, 3219999999),
                'sitio_web' => 'https://www.' . Str::lower(Str::random(6)) . '.com',
                'id_usuario' => $usuario->id,
                'id_estado_cliente' => $estadoDefault->id,
            ]);
        }
    }
}
