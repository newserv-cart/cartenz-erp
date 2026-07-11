<?php

namespace App\Services;

class PricingEngine
{
    protected TariffEngine $tariff;
    protected RuleEngine $rule;
    protected ChargeEngine $charge;

    public function __construct()
    {
        $this->tariff = new TariffEngine();
        $this->rule   = new RuleEngine();
        $this->charge = new ChargeEngine();
    }

    public function calculate(array $shipment): array
    {
        $base = $this->tariff->getBaseTariff($shipment);

        $context = array_merge($shipment, [
            'freight_charge' => (float) $base['selling_rate'],
            'vendor_freight' => (float) $base['vendor_rate'],
        ]);

        $matchedRules = $this->rule->evaluate($context);
        $charges      = $this->charge->calculate($context, $matchedRules);

        $customerChargeTotal = 0;
        $vendorChargeTotal   = 0;

        foreach ($charges as $charge) {
            $side   = strtoupper($charge['charge_side']);
            $amount = (float) $charge['amount'];

            if ($side === 'CUSTOMER') {
                $customerChargeTotal += $amount;
            } elseif ($side === 'VENDOR') {
                $vendorChargeTotal += $amount;
            } elseif ($side === 'BOTH') {
                $customerChargeTotal += $amount;
                $vendorChargeTotal   += $amount;
            }
        }

        $customerTotal =
            (float) $base['selling_rate'] + $customerChargeTotal;

        $vendorTotal =
            (float) $base['vendor_rate'] + $vendorChargeTotal;

        $profit = $customerTotal - $vendorTotal;

        $margin = $customerTotal > 0
            ? ($profit / $customerTotal) * 100
            : 0;

        return [
            'base_tariff'           => $base,
            'matched_rules'         => $matchedRules,
            'charges'               => $charges,
            'customer_charge_total' => round($customerChargeTotal, 2),
            'vendor_charge_total'   => round($vendorChargeTotal, 2),
            'customer_total'        => round($customerTotal, 2),
            'vendor_total'          => round($vendorTotal, 2),
            'profit'                => round($profit, 2),
            'margin_percent'        => round($margin, 2),
        ];
    }
}