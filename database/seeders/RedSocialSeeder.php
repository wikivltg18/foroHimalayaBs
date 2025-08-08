<?php

namespace Database\Seeders;

use App\Models\RedSocial;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RedSocialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $redesSociales = [
            [
                'nombre_rsocial' => 'Facebook',
                'url_rsocial' => 'https://facebook.com/mockuser001',
                'id_cliente' => 1
            ],
            [
                'nombre_rsocial' => 'Facebook',
                'url_rsocial' => 'https://facebook.com/profile_dev_test',
                'id_cliente' => 2
            ],
            [
                'nombre_rsocial' => 'Facebook',
                'url_rsocial' => 'https://facebook.com/demo.account92',
                'id_cliente' => 3
            ],
            [
                'nombre_rsocial' => 'Instagram',
                'url_rsocial' => 'https://instagram.com/fake_influencer01',
                'id_cliente' => 4
            ],
            [
                'nombre_rsocial' => 'Instagram',
                'url_rsocial' => 'https://instagram.com/test_demo_account',
                'id_cliente' => 5
            ],
            [
                'nombre_rsocial' => 'Instagram',
                'url_rsocial' => 'https://instagram.com/insta_dev_profile92',
                'id_cliente' => 6
            ],
            [
                'nombre_rsocial' => 'YouTube',
                'url_rsocial' => 'https://youtube.com/channel/UC000fakechannel01',
                'id_cliente' => 7
            ],
            [
                'nombre_rsocial' => 'YouTube',
                'url_rsocial' => 'https://youtube.com/user/demouser123',
                'id_cliente' => 8
            ],
            [
                'nombre_rsocial' => 'YouTube',
                'url_rsocial' => 'https://youtube.com/c/fake_creator_channel',
                'id_cliente' => 1
            ],
            [
                'nombre_rsocial' => 'YouTube',
                'url_rsocial' => 'https://youtube.com/watch?v=dev_video_test001',
                'id_cliente' => 2
            ]
        ];

        foreach ($redesSociales as $redsocial) {
            RedSocial::create($redsocial);
        }
    }
}