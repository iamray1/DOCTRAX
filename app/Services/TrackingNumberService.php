<?php

namespace App\Services;

use App\Models\TrackingCounter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TrackingNumberService
{
    private const DEFAULT_OFFICE = 'CSJDM';
    private const MIN_SEQUENCE_WIDTH = 6;

    /**
     * Generate a lifetime-unique tracking number.
     *
     * Format: {OFFICE}-{YYYY}-{MM}-{SEQUENCE}
     * Example: CSJDM-2026-02-000123
     *
     * The sequence has a minimum width of 6 digits and grows automatically
     * beyond that width when needed, preventing practical exhaustion.
     *
     * @param string|null $officeCode
     * @return array{tracking_number: string, office_code: string}
     * @throws \Throwable
     */
    public function generate(?string $officeCode = null): array
    {
        $officeCode = strtoupper($officeCode ?? self::DEFAULT_OFFICE);

        $now = Carbon::now('Asia/Manila');
        $year = $now->year;
        $month = $now->month;

        return DB::transaction(function () use ($officeCode, $year, $month) {
            $counter = TrackingCounter::where('office_code', $officeCode)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = TrackingCounter::create([
                    'office_code' => $officeCode,
                    'year' => $year,
                    'month' => $month,
                    'last_sequence' => 0,
                ]);

                $counter = TrackingCounter::where('id', $counter->id)
                    ->lockForUpdate()
                    ->first();
            }

            $nextSequence = $counter->last_sequence + 1;

            $counter->last_sequence = $nextSequence;
            $counter->save();

            $sequencePart = str_pad((string) $nextSequence, self::MIN_SEQUENCE_WIDTH, '0', STR_PAD_LEFT);
            $trackingNumber = sprintf('%s-%04d-%02d-%s', $officeCode, $year, $month, $sequencePart);

            return [
                'tracking_number' => $trackingNumber,
                'office_code' => $officeCode,
            ];
        });
    }
}
