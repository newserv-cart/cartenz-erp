<?php

namespace App\Services;

use App\Models\VendorModel;

class VendorRecommendationService
{
    protected VendorModel $vendorModel;
    protected VendorCostService $costService;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
        $this->costService = new VendorCostService();
    }

    public function compare(array $shipment): array
    {
        $customerCharge = round(
            (float) (
                $shipment['total_charge']
                ?? $shipment['selling_amount']
                ?? 0
            ),
            2
        );

        $vendors = $this->vendorModel
            ->where('status', 1)
            ->orderBy('vendor_priority', 'ASC')
            ->orderBy('vendor_name', 'ASC')
            ->findAll();

        $result = [];

        foreach ($vendors as $vendor) {
            $cost = $this->costService->calculateForVendor(
                $shipment,
                (int) $vendor['id']
            );

            if ($cost === null) {
                continue;
            }

            $vendorCost = round((float) $cost['vendor_cost'], 2);
            $profit     = round($customerCharge - $vendorCost, 2);

            $result[] = [
                'vendor'          => $vendor,
                'vendor_cost'     => $vendorCost,
                'customer_charge' => $customerCharge,
                'profit'          => $profit,
                'score'           => $profit,
                'cost_result'     => $cost,
            ];
        }

        usort(
            $result,
            static function (array $a, array $b): int {
                $costCompare = $a['vendor_cost'] <=> $b['vendor_cost'];

                if ($costCompare !== 0) {
                    return $costCompare;
                }

                $priorityCompare =
                    (int) ($a['vendor']['vendor_priority'] ?? 0)
                    <=>
                    (int) ($b['vendor']['vendor_priority'] ?? 0);

                if ($priorityCompare !== 0) {
                    return $priorityCompare;
                }

                return strcmp(
                    (string) $a['vendor']['vendor_name'],
                    (string) $b['vendor']['vendor_name']
                );
            }
        );

        foreach ($result as $index => &$row) {
            $row['rank_no']     = $index + 1;
            $row['recommended'] = $index === 0 ? 1 : 0;
        }
        unset($row);

        return $result;
    }
}
