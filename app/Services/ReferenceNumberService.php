<?php

namespace App\Services;

use App\Models\Document;
use RuntimeException;

class ReferenceNumberService
{
    private const LENGTH = 8;
    private const ALPHANUM = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Generate a unique 8-character uppercase alphanumeric reference number.
     *
     * 36^8 combinations (~2.8 trillion) keeps practical exhaustion out of reach.
     */
    public function generateUnique(int $maxAttempts = 30): string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $candidate = $this->generateCandidate();

            $exists = Document::whereRaw('UPPER(reference_number) = ?', [$candidate])->exists();
            if (!$exists) {
                return $candidate;
            }
        }

        throw new RuntimeException('Unable to generate unique reference number. Please try again.');
    }

    public function generateCandidate(): string
    {
        $out = '';
        $max = strlen(self::ALPHANUM) - 1;

        for ($i = 0; $i < self::LENGTH; $i++) {
            $out .= self::ALPHANUM[random_int(0, $max)];
        }

        return $out;
    }
}
