<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->boolean('is_school')->default(false)->after('is_active');
        });

        $schoolNames = collect(config('representative_offices', []))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(function ($name) {
                return strtolower($name);
            })
            ->values();

        if ($schoolNames->isNotEmpty()) {
            DB::table('offices')
                ->whereIn('name', $schoolNames->all())
                ->update(['is_school' => true]);
        }

        $legacySchoolNames = DB::table('users')
            ->where('account_type', 'representative')
            ->whereNotNull('representative_office_name')
            ->distinct()
            ->pluck('representative_office_name')
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(function ($name) {
                return strtolower($name);
            })
            ->values();

        if ($legacySchoolNames->isNotEmpty()) {
            DB::table('offices')
                ->whereIn('name', $legacySchoolNames->all())
                ->update(['is_school' => true]);
        }

        DB::table('offices')
            ->where('code', 'like', 'SCH\\_%')
            ->orWhere('code', 'like', 'SCH_%')
            ->update(['is_school' => true]);
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn('is_school');
        });
    }
};
