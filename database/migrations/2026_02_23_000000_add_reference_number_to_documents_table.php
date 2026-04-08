<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ALPHANUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const LENGTH = 8;

    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('reference_number', self::LENGTH)->nullable()->after('tracking_number');
        });

        DB::table('documents')
            ->select('id')
            ->whereNull('reference_number')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('documents')
                        ->where('id', $row->id)
                        ->update(['reference_number' => $this->generateUniqueReference()]);
                }
            });

        Schema::table('documents', function (Blueprint $table) {
            $table->unique('reference_number');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique(['reference_number']);
            $table->dropColumn('reference_number');
        });
    }

    private function generateUniqueReference(): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $candidate = $this->generateCandidate();

            $exists = DB::table('documents')
                ->whereRaw('UPPER(reference_number) = ?', [$candidate])
                ->exists();

            if (!$exists) {
                return $candidate;
            }
        }

        throw new RuntimeException('Failed generating unique reference numbers during migration.');
    }

    private function generateCandidate(): string
    {
        $output = '';
        $max = strlen(self::ALPHANUM) - 1;

        for ($i = 0; $i < self::LENGTH; $i++) {
            $output .= self::ALPHANUM[random_int(0, $max)];
        }

        return $output;
    }
};
