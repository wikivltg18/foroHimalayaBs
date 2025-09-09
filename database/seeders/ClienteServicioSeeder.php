<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\{Cliente, Servicio, MapaDelCliente, MapaArea, FaseServicio, FaseDeServicioInstancia, TipoServicio};

class ClienteServicioSeeder extends Seeder
{
    public function run(): void
    {
        // Cliente
        $cliente = Cliente::create([
            'nombre' => 'Comfandis',
            'correo_electronico' => 'info@comfandis.com',
            'telefono' => '123456789',
            'sitio_web' => 'https://www.comfandis.com',
            'id_usuario' => 9,           // ajusta según tu usuario
            'id_estado_cliente' => 1,    // ajusta según catálogo
        ]);

        $tipo = TipoServicio::where('nombre', 'Creación de parrilla')->first();

        // Servicio
        $servicio = Servicio::create([
            'cliente_id' => $cliente->id,
            'nombre_servicio' => 'Servicio 1',
            'modalidad_id' => $tipo->modalidad_id,
            'tipo_servicio_id' => $tipo->id,
        ]);

        // Mapa del cliente
        $mapa = MapaDelCliente::create(['servicio_id' => $servicio->id]);

        // Áreas base
        $areas = [
            ['id' => '1'],
            ['id' => '2'],
            ['id' => '3'],
            ['id' => '4'],
            ['id' => '5'],
        ];

        foreach ($areas as $a) {
            MapaArea::create([
                'mapa_del_cliente_id' => $mapa->id,
                'area_id' => $a['id'],
                'horas_contratadas' => 160,
            ]);
        }

        // Fases instanciadas desde plantilla
        $plantillas = FaseServicio::where('tipo_servicio_id', $tipo->id)->get();
        foreach ($plantillas as $i => $tpl) {
            FaseDeServicioInstancia::create([
                'servicio_id' => $servicio->id,
                'fase_servicio_id' => $tpl->id,
                'nombre' => $tpl->nombre,
                'descripcion' => $tpl->descripcion,
                'posicion' => $i + 1,
            ]);
        }
    }
}