<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use GuzzleHttp\Client;

class TestUbigeoSeeder extends Seeder
{
    public function run(): void
    {
        $client = new Client();
        $url = 'https://api.apis.net.pe/v1/ubigeo?all=true';

        try {
            $response = $client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);

            dump('🔍 Estructura completa devuelta por la API:');
            dd($data); // 👀 Mostrará todo el contenido real

        } catch (\Exception $e) {
            $this->command->error('Error: ' . $e->getMessage());
        }
    }
}
