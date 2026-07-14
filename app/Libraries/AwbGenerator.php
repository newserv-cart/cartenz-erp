<?php

namespace App\Libraries;

use App\Models\AwbSequenceModel;

class AwbGenerator
{
    public const PREFIX = 'CTZ';

    /**
     * CTZ + YY + MM + 5 digit running number.
     * Example: CTZ260700015
     */
    public const PUBLIC_PATTERN = '/^CTZ\d{9}$/';

    public const PUBLIC_EXAMPLE = 'CTZ260700015';

    public const PUBLIC_LENGTH = 12;

    public static function generate(): string
    {
        $yearFull  = date('Y');
        $yearShort = date('y');
        $month     = date('m');

        $model = new AwbSequenceModel();

        $sequence = $model
            ->where('awb_prefix', self::PREFIX)
            ->where('awb_year', $yearFull)
            ->where('awb_month', $month)
            ->first();

        if (!$sequence) {
            $model->insert([
                'awb_prefix' => self::PREFIX,
                'awb_year'   => $yearFull,
                'awb_month'  => $month,
                'last_number'=> 1,
            ]);

            $runningNumber = 1;
        } else {
            $runningNumber =
                (int) $sequence['last_number'] + 1;

            $model->update($sequence['id'], [
                'last_number' => $runningNumber,
            ]);
        }

        return sprintf(
            '%s%s%s%05d',
            self::PREFIX,
            $yearShort,
            $month,
            $runningNumber
        );
    }

    /**
     * Normalize casing only after the raw input passes validation.
     */
    public static function normalize($value): string
    {
        return strtoupper((string) $value);
    }

    /**
     * Strict public AWB validation.
     *
     * Accepted:
     * - CTZ260700015
     * - ctz260700015
     *
     * Rejected:
     * - leading/trailing/internal spaces
     * - URLs
     * - hyphens
     * - symbols
     * - incomplete numbers
     */
    public static function isValidPublicAwb($value): bool
    {
        $raw = (string) $value;

        if (
            $raw === ''
            || strlen($raw) !== self::PUBLIC_LENGTH
        ) {
            return false;
        }

        return preg_match(
            self::PUBLIC_PATTERN,
            strtoupper($raw)
        ) === 1;
    }
}
