<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\ExpressRateHeaderModel;
use App\Models\ExpressRateDetailModel;
use App\Models\VendorModel;
use App\Models\ServiceModel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExpressRateController extends BaseController
{
    protected $headerModel;
    protected $detailModel;

    public function __construct()
    {
        $this->headerModel = new ExpressRateHeaderModel();
        $this->detailModel = new ExpressRateDetailModel();
    }

    // Daftar semua tarif yang sudah diimpor
    public function index()
    {
        $headers = $this->headerModel
            ->select('
                express_rate_headers.*,
                vendors.name AS vendor_name,
                services.name AS service_name
            ')
            ->join('vendors', 'vendors.id = express_rate_headers.vendor_id', 'left')
            ->join('services', 'services.id = express_rate_headers.service_id', 'left')
            ->orderBy('express_rate_headers.id', 'DESC')
            ->findAll();

        // Hitung jumlah detail tarif per header
        foreach ($headers as &$row) {
            $row['total_rate'] = $this->detailModel
                ->where('header_id', $row['id'])
                ->countAllResults();
        }

        return view('master/express_rates/index', [
            'headers' => $headers
        ]);
    }

    // Halaman form upload
    public function upload()
    {
        $vendorModel  = new VendorModel();
        $serviceModel = new ServiceModel();

        return view('master/express_rates/upload', [
            'vendors'  => $vendorModel->findAll(),
            'services' => $serviceModel->findAll()
        ]);
    }

    // Proses baca Excel & simpan ke database
    public function import()
    {
        $file = $this->request->getFile('excel_file'); // Sesuaikan dengan nama input di form

        if (!$file->isValid() || $file->hasMoved()) {
            return redirect()->back()
                ->with('error', 'File Excel tidak valid atau rusak.');
        }

        try {
            // Baca file Excel
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // Simpan data HEADER
            $headerId = $this->headerModel->insert([
                'vendor_id'      => $this->request->getPost('vendor_id'),
                'service_id'     => $this->request->getPost('service_id'),
                'effective_date' => $this->request->getPost('effective_date'),
                'remarks'        => $this->request->getPost('remarks')
            ], true); // true = kembalikan ID yang baru dibuat

            $totalDoc = 0;
            $totalPkg = 0;

            // Lewati baris judul Excel
            foreach (array_slice($rows, 1) as $row) {
                if (empty($row[0])) continue;

                $shipmentType = strtoupper(trim($row[0]));
                $weight = (float)$row[1];

                // Kolom 2 sampai 9 = Zona 1 sampai 8
                for ($zone = 1; $zone <= 8; $zone++) {
                    $price = isset($row[$zone + 1]) ? (float)$row[$zone + 1] : 0;

                    if ($price <= 0) continue;

                    $this->detailModel->insert([
                        'header_id'      => $headerId,
                        'shipment_type'  => $shipmentType,
                        'zone'           => $zone,
                        'weight'         => $weight,
                        'price'          => $price
                    ]);

                    if ($shipmentType === 'DOCUMENT') $totalDoc++;
                    if ($shipmentType === 'PACKAGE') $totalPkg++;
                }
            }

            $totalAll = $totalDoc + $totalPkg;

            return redirect()->to(site_url('master/express-rates'))
                ->with('success', "✅ Import selesai<br>
                    Shipment Type :<br>
                    - Document : {$totalDoc} baris<br>
                    - Package : {$totalPkg} baris<br>
                    <strong>Total Imported : {$totalAll} Tarif</strong>");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }
}