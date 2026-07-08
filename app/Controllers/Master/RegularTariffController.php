<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;

use App\Models\RegularTariffModel;
use App\Models\CountryModel;
use App\Models\ServiceModel;
use App\Models\PackageTypeModel;
use App\Models\AgentTypeModel;
use App\Models\DeliveryTypeModel;
use App\Models\WeightRangeModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RegularTariffController extends BaseController
{
    public function index()
    {
        $data = [
            'title'     => 'Regular Tariff',
            'countries' => (new CountryModel())
                ->where('status', 1)
                ->orderBy('country_name')
                ->findAll(),
            'services'  => (new ServiceModel())
                ->where('status', 1)
                ->orderBy('service_name')
                ->findAll()
        ];

        return view('master/regular_tariffs/index', $data);
    }

    public function store()
    {
        $model = new RegularTariffModel();

        $countryId       = (int)$this->request->getPost('country_id');
        $serviceId       = (int)$this->request->getPost('service_id');
        $agentTypeId     = (int)$this->request->getPost('agent_type_id');
        $deliveryTypeId  = (int)$this->request->getPost('delivery_type_id') ?? null;
        $packageTypeId   = (int)$this->request->getPost('package_type_id') ?? null;

        $weightFrom      = (float)$this->request->getPost('weight_from');
        $weightTo        = (float)$this->request->getPost('weight_to');

        // Validasi dasar
        if ($countryId <= 0 || $serviceId <= 0 || $agentTypeId <= 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Negara, Layanan, dan Tipe Agen wajib diisi.');
        }

        if ($weightFrom <= 0 || $weightTo < $weightFrom) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Rentang berat tidak valid.');
        }

        // Cek tumpang tindih berat
        $exists = $model
            ->where('country_id', $countryId)
            ->where('service_id', $serviceId)
            ->where('agent_type_id', $agentTypeId)
            ->groupStart()
                ->where('weight_from <=', $weightTo)
                ->where('weight_to >=', $weightFrom)
            ->groupEnd()
            ->first();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Rentang berat bertabrakan dengan tarif yang sudah ada.');
        }

        // Simpan data
        $model->insert([
            'country_id'       => $countryId,
            'service_id'       => $serviceId,
            'agent_type_id'    => $agentTypeId,
            'delivery_type_id' => $deliveryTypeId,
            'package_type_id'  => $packageTypeId,
            'weight_from'      => $weightFrom,
            'weight_to'        => $weightTo,
            'selling_price'     => $this->request->getPost('selling_price'),
            'minimum_charge'    => $this->request->getPost('minimum_charge') ?? 0,
            'effective_from'   => $this->request->getPost('effective_from') ?? date('Y-m-d'),
            'effective_until'  => $this->request->getPost('effective_until') ?? null,
            'remarks'          => $this->request->getPost('remarks'),
            'status'           => 1,
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s')
        ]);

        return redirect()
            ->to(site_url('master/regular-tariffs'))
            ->with('success', 'Tarif berhasil disimpan.');
    }

    public function manual()
    {
        $data = [
            'title'     => 'Manual Regular Tariff',
            'countries' => (new CountryModel())
                ->where('status', 1)
                ->orderBy('country_name')
                ->findAll(),
            'services'  => (new ServiceModel())
                ->where('status', 1)
                ->orderBy('service_name')
                ->findAll()
        ];

        return view('master/regular_tariffs/manual_input', $data);
    }

    public function saveManual()
{       
    $db    = \Config\Database::connect();
    $model = new \App\Models\RegularTariffModel();

    $countryId   = (int)($this->request->getPost('country_id') ?? 0);
    $serviceId   = (int)($this->request->getPost('service_id') ?? 0);

    $from     = $this->request->getPost('weight_from') ?? [];
    $to       = $this->request->getPost('weight_to') ?? [];
    $agentA   = $this->request->getPost('agent_a_price') ?? [];
    $agentB   = $this->request->getPost('agent_b_price') ?? [];
    $agentC   = $this->request->getPost('agent_c_price') ?? [];
    $publish  = $this->request->getPost('publish_price') ?? [];
    $vendor   = $this->request->getPost('vendor_cost') ?? [];

    // Validasi awal
    if ($countryId <= 0 || $serviceId <= 0) {
        return redirect()->back()->with('error', 'Pilih Negara dan Layanan terlebih dahulu!');
    }

    if (!is_array($from) || empty($from)) {
        return redirect()->back()->with('error', 'Data berat tidak boleh kosong!');
    }

    $db->transStart();

    // Hapus data lama untuk negara + layanan ini
    $model
        ->where('country_id', $countryId)
        ->where('service_id', $serviceId)
        ->delete();

    $rows = [];
    foreach ($from as $i => $val) {
        // Bersihkan dan ubah koma ke titik
        $wFrom = (float)str_replace(',', '.', trim($val));
        $wTo   = isset($to[$i]) ? (float)str_replace(',', '.', trim($to[$i])) : 0;

        // Hanya cek beratnya saja, harga boleh 0 sementara
        if ($wFrom <= 0 || $wTo < $wFrom) {
            continue;
        }

        // Proses harga juga ubah koma ke titik
        $rows[] = [
            'country_id'      => $countryId,
            'service_id'      => $serviceId,
            'weight_from'     => $wFrom,
            'weight_to'       => $wTo,
            'agent_a_price'   => isset($agentA[$i]) ? (float)str_replace(',', '.', trim($agentA[$i])) : 0,
            'agent_b_price'   => isset($agentB[$i]) ? (float)str_replace(',', '.', trim($agentB[$i])) : 0,
            'agent_c_price'   => isset($agentC[$i]) ? (float)str_replace(',', '.', trim($agentC[$i])) : 0,
            'publish_price'   => isset($publish[$i]) ? (float)str_replace(',', '.', trim($publish[$i])) : 0,
            'vendor_cost'     => isset($vendor[$i]) ? (float)str_replace(',', '.', trim($vendor[$i])) : 0,
            'status'          => 1,
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ];
    }

    if (empty($rows)) {
        $db->transComplete();
        return redirect()->back()->with('error', 'Tidak ada data yang valid! Cek rentang beratnya.');
    }

    $model->insertBatch($rows);

    $db->transComplete();

    return redirect()
        ->back()
        ->with('success', '✅ Tarif disimpan! Jumlah baris: ' . count($rows));
}

    public function upload()
    {
        $data = [
            'countries' => (new CountryModel())
                ->where('status', 1)
                ->orderBy('country_name')
                ->findAll(),
            'services'  => (new ServiceModel())
                ->where('status', 1)
                ->orderBy('service_name')
                ->findAll()
        ];

        return view('master/regular_tariffs/upload', $data);
    }

    public function downloadTemplate()
    {   
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Regular Tariff');

        $header = [
            'A1' => 'Weight From',
            'B1' => 'Weight To',
            'C1' => 'Agent A',
            'D1' => 'Agent B',
            'E1' => 'Agent C',
            'F1' => 'Publish Price',
            'G1' => 'Vendor Cost'
        ];

        foreach ($header as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D6EFD']
            ]
        ]);

        $ranges = [
            ['from' => 1, 'to' => 1],
            ['from' => 2, 'to' => 5],
            ['from' => 6, 'to' => 29],
            ['from' => 30, 'to' => 99],
            ['from' => 100, 'to' => 299]
        ];

        $row = 2;
        foreach ($ranges as $r) {
            $sheet->setCellValue("A$row", $r['from']);
            $sheet->setCellValue("B$row", $r['to']);
            $row++;
        }

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle("A1:G".($row-1))
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $filename = 'Regular_Tariff_Template.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function cloneTariff()
    {
        return view('master/regular_tariffs/clone');
    }

    public function loadTariff()
    {
        $model = new RegularTariffModel();

        $countryId = (int)($this->request->getGet('country_id') ?? 0);
        $serviceId = (int)($this->request->getGet('service_id') ?? 0);

        if ($countryId <= 0 || $serviceId <= 0) {
            return $this->response->setJSON([]);
        }

        $rows = $model
            ->where('country_id', $countryId)
            ->where('service_id', $serviceId)
            ->where('status', 1)
            ->orderBy('weight_from', 'ASC')
            ->findAll();

        return $this->response->setJSON($rows);
    }

    public function import()
    {
        $file = $this->request->getFile('excel');

        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid.');
        }

        $countryId = (int)$this->request->getPost('country_id');
        $serviceId = (int)$this->request->getPost('service_id');

        if ($countryId <= 0 || $serviceId <= 0) {
            return redirect()->back()->with('error', 'Negara dan Layanan wajib dipilih.');
        }

        try {
            $spreadsheet = IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'File kosong atau format salah.');
            }

            $model = new RegularTariffModel();
            $db = \Config\Database::connect();
            $db->transStart();

            // Hapus data lama
            $model->where('country_id', $countryId)->where('service_id', $serviceId)->delete();

            $insert = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $wFrom = (float)str_replace(',', '.', trim($row[0] ?? ''));
                $wTo   = (float)str_replace(',', '.', trim($row[1] ?? ''));

                if ($wFrom <= 0 || $wTo < $wFrom) continue;

                $insert[] = [
                    'country_id'      => $countryId,
                    'service_id'      => $serviceId,
                    'weight_from'     => $wFrom,
                    'weight_to'       => $wTo,
                    'agent_a_price'   => (float)($row[2] ?? 0),
                    'agent_b_price'   => (float)($row[3] ?? 0),
                    'agent_c_price'   => (float)($row[4] ?? 0),
                    'publish_price'   => (float)($row[5] ?? 0),
                    'vendor_cost'     => (float)($row[6] ?? 0),
                    'status'          => 1,
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s')
                ];
            }

            if (!empty($insert)) {
                $model->insertBatch($insert);
            }

            $db->transComplete();
            return redirect()->back()->with('success', 'Import berhasil! Jumlah data: ' . count($insert));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}