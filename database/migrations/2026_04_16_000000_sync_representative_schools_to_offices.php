<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $schools = collect(config('representative_offices', []))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(function ($name) {
                return strtolower($name);
            })
            ->values();

        foreach ($schools as $schoolName) {
            $office = DB::table('offices')
                ->whereRaw('LOWER(name) = ?', [strtolower($schoolName)])
                ->first();

            if (!$office) {
                $code = $this->generateUniqueCode($schoolName);

                DB::table('offices')->insert([
                    'code' => $code,
                    'name' => $schoolName,
                    'head' => null,
                    'description' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $office = DB::table('offices')
                    ->whereRaw('LOWER(name) = ?', [strtolower($schoolName)])
                    ->first();
            }

            if (!$office) {
                continue;
            }

            DB::table('users')
                ->where('account_type', 'representative')
                ->whereNull('office_id')
                ->whereRaw('LOWER(TRIM(COALESCE(representative_office_name, \'\'))) = ?', [strtolower($schoolName)])
                ->update([
                    'office_id' => $office->id,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $schoolNames = collect(config('representative_offices', []))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(function ($name) {
                return strtolower($name);
            })
            ->values();

        DB::table('users')
            ->where('account_type', 'representative')
            ->whereNotNull('office_id')
            ->whereIn('representative_office_name', $schoolNames->all())
            ->update([
                'office_id' => null,
                'updated_at' => now(),
            ]);
    }

    private function generateUniqueCode(string $schoolName): string
    {
        $normalized = preg_replace('/\s+/', '_', $schoolName);
        $normalized = str_replace('-', '_', (string) $normalized);
        $normalized = preg_replace('/[^A-Z0-9_]/i', '', (string) $normalized);
        $base = strtoupper(trim((string) $normalized));
        $base = $base !== '' ? substr($base, 0, 16) : 'SCHOOL';
        $candidate = 'SCH_' . $base;
        $candidate = substr($candidate, 0, 20);
        $suffix = 1;

        while (DB::table('offices')->where('code', $candidate)->exists()) {
            $suffixText = (string) $suffix;
            $maxBaseLength = max(1, 20 - 4 - strlen($suffixText) - 1);
            $candidate = 'SCH_' . substr($base, 0, $maxBaseLength) . '_' . $suffixText;
            $suffix++;

            if ($suffix > 999) {
                throw new RuntimeException('Unable to generate a unique code for school office sync.');
            }
        }

        return $candidate;
    }
};
