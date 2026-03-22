<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'john@quvio.com')->first();

        $clients = [
            [
                'name'    => 'Alice Martin',
                'email'   => 'alice@acmecorp.com',
                'phone'   => '+33 6 12 34 56 78',
                'company' => 'Acme Corp',
                'address' => '12 Rue de la Paix, 75001 Paris',
            ],
            [
                'name'    => 'Bob Johnson',
                'email'   => 'bob@techstart.io',
                'phone'   => '+33 6 98 76 54 32',
                'company' => 'TechStart',
                'address' => '45 Avenue des Champs, 75008 Paris',
            ],
            [
                'name'    => 'Sophie Dupont',
                'email'   => 'sophie@designstudio.fr',
                'phone'   => '+33 6 55 44 33 22',
                'company' => 'Design Studio',
                'address' => '8 Rue du Commerce, 69001 Lyon',
            ],
            [
                'name'    => 'Marc Lefebvre',
                'email'   => 'marc@lefebvre.com',
                'phone'   => '+33 6 11 22 33 44',
                'company' => null,
                'address' => '22 Rue Victor Hugo, 13001 Marseille',
            ],
            [
                'name'    => 'Emma Wilson',
                'email'   => 'emma@globaltrading.eu',
                'phone'   => '+33 6 77 88 99 00',
                'company' => 'Global Trading EU',
                'address' => '3 Place Bellecour, 69002 Lyon',
            ],
            [
                'name'    => 'Thomas Bernard',
                'email'   => 'thomas@bernard-consulting.fr',
                'phone'   => '+33 6 33 44 55 66',
                'company' => 'Bernard Consulting',
                'address' => '15 Rue de la République, 67000 Strasbourg',
            ],
        ];

        foreach ($clients as $data) {
            Client::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'email'   => $data['email'],
                ],
                array_merge($data, ['user_id' => $user->id])
            );
        }

        $this->command->info('✅ 6 clients created');
    }
}