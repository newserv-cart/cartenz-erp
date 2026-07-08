<?php

namespace App\Libraries;

use App\Models\AwbSequenceModel;

class AwbGenerator
{
    public static function generate()
    {
        $prefix = 'CTZ';

        $yearFull = date('Y');
        $yearShort = date('y');
        $month = date('m');

        $model = new AwbSequenceModel();

        $sequence = $model
            ->where('awb_prefix', $prefix)
            ->where('awb_year', $yearFull)
            ->where('awb_month', $month)
            ->first();

        if (!$sequence) {

            $model->insert([
                'awb_prefix' => $prefix,
                'awb_year'   => $yearFull,
                'awb_month'  => $month,
                'last_number'=> 1,
            ]);

            $runningNumber = 1;

        } else {

            $runningNumber = $sequence['last_number'] + 1;

            $model->update($sequence['id'], [
                'last_number' => $runningNumber,
            ]);
        }

        return sprintf(
            '%s%s%s%05d',
            $prefix,
            $yearShort,
            $month,
            $runningNumber
        );
    }
}