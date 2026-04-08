<?php

namespace App\Support;

class UiNumber
{
    public static function compact($value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        if (! is_numeric($value)) {
            $normalized = str_replace(',', '', trim((string) $value));
            if (! is_numeric($normalized)) {
                return (string) $value;
            }

            $value = $normalized;
        }

        $number = (float) $value;
        $abs = abs($number);

        if ($abs < 1000) {
            return (string) (int) round($number);
        }

        $units = ['K', 'M', 'B', 'T'];
        $unitIndex = -1;

        while ($abs >= 1000 && $unitIndex < count($units) - 1) {
            $number /= 1000;
            $abs /= 1000;
            $unitIndex++;
        }

        $rounded = round($number, 1);
        if (abs($rounded) >= 1000 && $unitIndex < count($units) - 1) {
            $number = $rounded / 1000;
            $unitIndex++;
        }

        $formatted = number_format($number, 1, '.', '');
        $formatted = rtrim(rtrim($formatted, '0'), '.');

        return $formatted . $units[$unitIndex];
    }
}
