<?php

namespace App\Support;

use InvalidArgumentException;

final class Money
{
    private const INPUT_PATTERN = '/^\d+(?:\.\d{1,2})?$/';

    public static function roundDivToCents(int $numeratorCents, int $denominatorUnits): int
    {
        if ($denominatorUnits <= 0) {
            return 0;
        }

        $q = intdiv($numeratorCents, $denominatorUnits);
        $r = $numeratorCents % $denominatorUnits;

        return ($r * 2 >= $denominatorUnits) ? $q + 1 : $q;
    }

    public static function centsToString(int $cents): string
    {
        $sign = $cents < 0 ? '-' : '';
        $abs = abs($cents);
        $whole = intdiv($abs, 100);
        $fraction = str_pad((string) ($abs % 100), 2, '0', STR_PAD_LEFT);

        return $sign.$whole.'.'.$fraction;
    }

    public static function inputToCents(string $rawAmount): int
    {
        $normalized = str_replace(',', '', trim($rawAmount));

        if (! preg_match(self::INPUT_PATTERN, $normalized)) {
            throw new InvalidArgumentException('Amount must be a non-negative number with up to 2 decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $normalized, 2), 2, '0');
        $fraction = str_pad($fraction, 2, '0', STR_PAD_RIGHT);

        return ((int) $whole * 100) + (int) $fraction;
    }
}

