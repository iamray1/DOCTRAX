<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['code' => 'OSDS',     'name' => 'Office of the Schools Division Superintendent',          'description' => 'Office of the SDS'],
            ['code' => 'OASDS',    'name' => 'Office of the Assistant Schools Division Superintendent','description' => 'Office of the ASDS'],
            ['code' => 'ADMIN',    'name' => 'Administrative Services',                                'description' => 'Administrative services and support'],
            ['code' => 'PERSONNEL','name' => 'Personnel Section',                                      'description' => 'Personnel and HR services'],
            ['code' => 'RECORDS',  'name' => 'Records Section',                                        'description' => 'Handles official records and document routing'],
            ['code' => 'CASHIER',  'name' => 'Cashiering Section',                                    'description' => 'Cash handling and disbursement'],
            ['code' => 'SUPPLY',   'name' => 'Property and Supply Section',                            'description' => 'Procurement, supply, and property management'],
            ['code' => 'ACCTG',    'name' => 'Accounting',                                             'description' => 'Financial and accounting services'],
            ['code' => 'BUDGET',   'name' => 'Budget',                                                 'description' => 'Budget management and allocation'],
            ['code' => 'LEGAL',    'name' => 'Legal Services',                                         'description' => 'Legal affairs and compliance'],
            ['code' => 'ICTU',     'name' => 'Information and Communications Technology Unit',         'description' => 'ICT services and support'],
            ['code' => 'PAYROLL',  'name' => 'Payroll Section',                                        'description' => 'Payroll processing and management'],
            ['code' => 'CID',      'name' => 'Curriculum Implementation Division',                     'description' => 'Curriculum development and implementation'],
            ['code' => 'HRMD',     'name' => 'Human Resource Management and Development',              'description' => 'HR management and staff development'],
            ['code' => 'PLANNING',  'name' => 'Planning and Research Section',                         'description' => 'Planning and research services'],
            ['code' => 'SGOD',     'name' => 'School Governance and Operations Division',              'description' => 'School governance and operations'],
        ];

        // Codes that should remain active
        $activeCodes = array_column($offices, 'code');

        // Upsert each office by code
        foreach ($offices as $office) {
            Office::updateOrCreate(
                ['code' => $office['code']],
                array_merge($office, ['is_active' => true])
            );
        }

        // Deactivate any old offices not in the new list
        Office::whereNotIn('code', $activeCodes)->update(['is_active' => false]);
    }
}
