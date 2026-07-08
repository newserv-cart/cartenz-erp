<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\ShipmentCalculationService;

class PricingController extends BaseController
{
    public function calculate()
    {
        // Pastikan hanya bisa diakses lewat AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Invalid Request'
            ]);
        }

        try {
            // Ambil SEMUA data yang dikirim dari JavaScript
            $post = $this->request->getPost();

            // Inisialisasi service dan jalankan perhitungan
            $shipmentService = new ShipmentCalculationService();
            $result = $shipmentService->calculate($post);

            // Kembalikan hasil dalam format yang diminta
            return $this->response->setJSON([
                'status' => 'success',
                'data'   => $result
            ]);

        } catch (\Throwable $e) {
            // Tangkap error apapun yang terjadi
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}