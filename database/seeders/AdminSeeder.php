<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Office;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Arthur Francisco — Super Admin & ICT office representative
        $ictuOffice = Office::where('code', 'ICTU')->first();

        User::updateOrCreate(
            ['email' => 'arthur.francisco@deped.gov.ph'],
            [
                'name'              => 'Arthur Francisco',
                'password'          => Hash::make('Admin123!'),
                'status'            => 'active',
                'role'              => 'superadmin',
                'account_type'      => 'representative',
                'office_id'         => $ictuOffice?->id,
                'email_verified_at' => now(),
                'activated_at'      => now(),
            ]
        );
    }
}
