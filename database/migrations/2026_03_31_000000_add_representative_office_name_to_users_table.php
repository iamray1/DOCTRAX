<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('representative_office_name')->nullable()->after('office_id');
        });

        DB::table('users')
            ->select(['id', 'name'])
            ->where('account_type', 'representative')
            ->whereNull('office_id')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    [$officeName, $displayName] = $this->splitRepresentativeName((string) $user->name);

                    if (!$officeName) {
                        continue;
                    }

                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'name' => $displayName,
                            'representative_office_name' => $officeName,
                        ]);
                }
            });
    }

    public function down(): void
    {
        DB::table('users')
            ->select(['id', 'name', 'representative_office_name'])
            ->where('account_type', 'representative')
            ->whereNull('office_id')
            ->whereNotNull('representative_office_name')
            ->orderBy('id')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'name' => trim($user->representative_office_name . ' - ' . $user->name),
                        ]);
                }
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('representative_office_name');
        });
    }

    private function splitRepresentativeName(string $rawName): array
    {
        $rawName = trim($rawName);

        if (!str_contains($rawName, ' - ')) {
            return [null, $rawName];
        }

        [$officeName, $displayName] = explode(' - ', $rawName, 2);

        $officeName = trim($officeName);
        $displayName = trim($displayName);

        if ($officeName === '' || $displayName === '') {
            return [null, $rawName];
        }

        return [$officeName, $displayName];
    }
};
