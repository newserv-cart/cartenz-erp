<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ManifestModel;
use App\Models\ManifestDetailModel;
use App\Models\ShipmentModel;
use Dompdf\Dompdf;
use App\Models\ShipmentTrackingModel;
class ManifestController extends BaseController
{
    public function index()
    {
        $data['manifests'] = (new ManifestModel())
            ->orderBy('id', 'DESC')
            ->findAll();

        return view(
            'manifests/index',
            $data
        );
    }

    public function create()
    {
        return view('manifests/create');
    }

    public function store()
    {
        $manifestNo =
            'MNF' .
            date('ym') .
            str_pad(
                rand(1,99999),
                5,
                '0',
                STR_PAD_LEFT
            );

        (new ManifestModel())->insert([
            'manifest_no'    => $manifestNo,
            'manifest_date'  => date('Y-m-d'),
            'status'         => 'OPEN',
            'total_shipment' => 0,
            'total_weight'   => 0,
        ]);

        return redirect()
            ->to('/manifests')
            ->with(
                'success',
                'Manifest Created'
            );
    }

    public function view($id)
    {
        $manifestModel = new ManifestModel();
        $detailModel   = new ManifestDetailModel();
        $shipmentModel = new ShipmentModel();

        $manifest = $manifestModel->find($id);

        $details = $detailModel
            ->where('manifest_id', $id)
            ->findAll();

       

$allManifestShipments = $detailModel
->select('shipment_id')
->findAll();

$usedShipmentIds = array_column(
$allManifestShipments,
'shipment_id'
);

$shipments = $shipmentModel
->orderBy('id', 'DESC');

if (!empty($usedShipmentIds)) {
$shipments->whereNotIn(
'id',
$usedShipmentIds
);
}

$shipments = $shipments->findAll();


        $shipments = $shipmentModel
            ->orderBy('id','DESC');

        if (!empty($usedShipmentIds))
        {
            $shipments->whereNotIn(
                'id',
                $usedShipmentIds
            );
        }

        $shipments = $shipments->findAll();





        return view(
            'manifests/view',
            [
                'manifest'  => $manifest,
                'details'   => $details,
                'shipments' => $shipments,
            ]
        );
    }

    public function addShipment($manifestId)
{
    $shipmentIds = $this->request->getPost('shipment_ids');


    
    if (empty($shipmentIds))
    {
        return redirect()->back()
            ->with('error', 'Pilih shipment terlebih dahulu');
    }


    

    $shipmentModel = new ShipmentModel();
    $detailModel   = new ManifestDetailModel();
    $manifestModel = new ManifestModel();

    $totalWeight = 0;
    $added = 0;
    foreach ($shipmentIds as $shipmentId)


    {
        $exists = $detailModel
    ->where('shipment_id', $shipmentId)
    ->first();

if ($exists)
{
    continue;
}

        $shipment = $shipmentModel->find($shipmentId);

        if (!$shipment)
        {
            continue;
        }

        $detailModel->insert([
            'manifest_id'   => $manifestId,
            'shipment_id'   => $shipment['id'],
            'awb_no'        => $shipment['awb_no'],

             
            'actual_weight' =>
(
    $shipment['chargeable_weight'] > 0
)
? $shipment['chargeable_weight']
: $shipment['actual_weight']
        ]);
        $added++;

        $totalWeight +=
(
$shipment['chargeable_weight'] > 0
)
? $shipment['chargeable_weight']
: $shipment['actual_weight'];

    }

    $totalShipment = $detailModel
        ->where('manifest_id', $manifestId)
        ->countAllResults();

    $sumWeight = $detailModel
        ->selectSum('actual_weight')
        ->where('manifest_id', $manifestId)
        ->first();

    $manifestModel->update(
        $manifestId,
        [
            'total_shipment' => $totalShipment,
            'total_weight'   => $sumWeight['actual_weight'] ?? 0
        ]
    );

   if ($added == 0)
{
return redirect()->back()
->with(
'error',
'Semua shipment yang dipilih sudah ada dalam manifest'
);
}

return redirect()->back()
->with(
'success',
$added . ' shipment berhasil ditambahkan'
);

}

   public function dispatch($manifestId)
{
    $manifestModel = new ManifestModel();
    $detailModel   = new ManifestDetailModel();
    $shipmentModel = new ShipmentModel();
    $trackingModel = new ShipmentTrackingModel();
    $manifest = $manifestModel->find($manifestId);

    $manifestModel->update($manifestId, [
        'status' => 'DISPATCHED'
    ]);

    $details = $detailModel
        ->where('manifest_id', $manifestId)
        ->findAll();

    foreach ($details as $detail)
    {
        $shipmentModel->update(
            $detail['shipment_id'],
            [
                'shipment_status' => 'IN_TRANSIT_HUB'
            ]
        );

     $trackingModel->insert([
            'shipment_id'       => $detail['shipment_id'],
            'tracking_code'     => $detail['awb_no'],
            'tracking_status'   => 'IN_TRANSIT_HUB',
            'tracking_location' => 'Origin Hub',
            'tracking_notes'    => 'Manifest Dispatched ' . $manifest['manifest_no'],
            'event_time'        => date('Y-m-d H:i:s')
        ]);

    }

    return redirect()->back()
        ->with('success', 'Manifest Dispatched');
}

   public function receive($manifestId)
{
    $manifestModel = new ManifestModel();
    $detailModel   = new ManifestDetailModel();
    $shipmentModel = new ShipmentModel();
    $trackingModel = new ShipmentTrackingModel();
    $manifest = $manifestModel->find($manifestId);
    

    $manifestModel->update($manifestId, [
        'status' => 'RECEIVED'
    ]);

    $details = $detailModel
        ->where('manifest_id', $manifestId)
        ->findAll();

    foreach ($details as $detail)
    {
        $shipmentModel->update(
            $detail['shipment_id'],
            [
                'shipment_status' => 'ARRIVED_HUB'
            ]
        );

         $trackingModel->insert([
            'shipment_id'       => $detail['shipment_id'],
            'tracking_code'     => $detail['awb_no'],
            'tracking_status'   => 'ARRIVED_HUB',
            'tracking_location' => 'Destination Hub',
            'tracking_notes'    => 'Manifest Received ' . $manifest['manifest_no'],
            'event_time'        => date('Y-m-d H:i:s')
        ]);
    }

    return redirect()->back()
        ->with('success', 'Manifest Received');
}

    public function close($manifestId)
    {
        (new ManifestModel())
            ->update(
                $manifestId,
                [
                    'status' => 'CLOSED'
                ]
            );

        return redirect()
            ->back()
            ->with(
                'success',
                'Manifest Closed'
            );
    }

    public function printManifest($id)
    {
        return redirect()->to(
            '/manifest/print/' . $id
        );
    }

    public function print($id)
{
    $manifestModel = new ManifestModel();
    $detailModel   = new ManifestDetailModel();

    $manifest = $manifestModel->find($id);

    $details = $detailModel
        ->where('manifest_id', $id)
        ->findAll();

    $html = view(
        'manifests/pdf',
        [
            'manifest' => $manifest,
            'details'  => $details
        ]
    );

    $dompdf = new Dompdf();

    $dompdf->loadHtml($html);

    $dompdf->setPaper('A4', 'portrait');

    $dompdf->render();

    $dompdf->stream(
        $manifest['manifest_no'] . '.pdf',
        ['Attachment' => false]
    );
}

public function scan()
{
    return view('manifests/scan');
}

public function scanSearch()
{
    $manifestNo = trim(
        $this->request->getPost('manifest_no')
    );

    $manifest = (new ManifestModel())
        ->where(
            'manifest_no',
            $manifestNo
        )
        ->first();

    if (!$manifest)
    {
        return redirect()
            ->back()
            ->with(
                'error',
                'Manifest tidak ditemukan'
            );
    }

    return redirect()->to(
        '/manifests/view/' .
        $manifest['id']
    );
}

}
