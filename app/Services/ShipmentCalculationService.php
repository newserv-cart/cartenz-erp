<?php

namespace App\Services;

use App\Models\ServiceModel;

class ShipmentCalculationService
{
    protected ServiceModel $serviceModel;

    public function __construct()
    {
        $this->serviceModel = new ServiceModel();
    }







   public function calculate(array $data): array
{
    $serviceId = (int) ($data['service_id'] ?? 0);

    if ($this->isExpress($serviceId)) {

        $engine = new ExpressCalculationService();

    } else {

        $engine = new RegularCalculationService();

    }

    $result = $engine->calculate($data);

    return $result;
}

    protected function isExpress(int $serviceId): bool
    {
        $service = $this->serviceModel->find($serviceId);

        if (!$service) {
            return false;
        }

        return strtoupper(trim($service['service_code'])) === 'EXPRESS';
    }
}