<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AreaSeeder;
use Database\Seeders\CargoSeeder;
use Database\Seeders\ClienteSeeder;
use Database\Seeders\ModalidadSeeder;
use Database\Seeders\RedSocialSeeder;
use Database\Seeders\TipoContratoSeeder;
use Database\Seeders\TipoServicioSeeder;
use Database\Seeders\FaseServiciosSeeder;
use Database\Seeders\ClienteServicioSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call([
            AreaSeeder::class,
            CargoSeeder::class,
            RolesAndPermissionsSeeder::class,
            EstadoClienteSeeder::class,
            ClienteSeeder::class,
            TipoContratoSeeder::class,
            RedSocialSeeder::class,
            ModalidadSeeder::class,
            TipoServicioSeeder::class,
            FaseServiciosSeeder::class,
            ClienteServicioSeeder::class,
        ]);
    }
}