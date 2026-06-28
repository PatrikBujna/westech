<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Decimal money arithmetic backed by bcmath, so monetary values never pass
 * through binary floating point (where 0.1 + 0.2 !== 0.3) and lose precision.
 */
final class Money
{
    /**
     * Compute the gross price (net + VAT), rounded half-up to $scale decimals.
     *
     * gross = net * (100 + vatRate) / 100
     *
     * @param string $net      Net price as a decimal string, e.g. "100.00".
     * @param string $vatRate  VAT rate (percent) as a decimal string, e.g. "23.00".
     * @param int    $scale    Decimal places in the result.
     * @return string
     */
    public static function gross(string $net, string $vatRate, int $scale = 2): string
    {
        $work = $scale + 4;
        $factor = bcadd('100', $vatRate, $work);
        $gross = bcdiv(bcmul($net, $factor, $work), '100', $work);

        return self::round($gross, $scale);
    }

    /**
     * Normalise a numeric string to a fixed-scale decimal string, rounded
     * half-up (e.g. "9.999" -> "10.00").
     *
     * @param string $amount
     * @param int    $scale
     * @return string
     */
    public static function normalize(string $amount, int $scale = 2): string
    {
        return self::round($amount, $scale);
    }

    /**
     * Round a decimal string half-up to $scale decimals. bcmath truncates, so
     * we nudge by half a unit (away from zero) before letting bcadd/bcsub
     * truncate to the target scale.
     *
     * @param string $number
     * @param int    $scale
     * @return string
     */
    private static function round(string $number, int $scale): string
    {
        $half = '0.' . str_repeat('0', $scale) . '5';

        if (bccomp($number, '0', $scale + 1) >= 0) {
            return bcadd($number, $half, $scale);
        }

        return bcsub($number, $half, $scale);
    }
}
