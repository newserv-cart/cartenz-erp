<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\FuelRuleModel;
use App\Models\ServiceModel;

class FuelRuleController extends BaseController
{
    protected $fuelModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->fuelModel = new FuelRuleModel();
        $this->serviceModel = new ServiceModel();
    }

    public function index()
    {
        $data = [

            'title' => 'Fuel Rules',

            'rules' => $this->fuelModel

                ->select('fuel_rules.*, services.service_name')

                ->join(
                    'services',
                    'services.id=fuel_rules.service_id',
                    'left'
                )

                ->orderBy('service_name')

                ->paginate(20),

            'pager' => $this->fuelModel->pager

        ];

        return view('master/fuel_rules/index', $data);
    }

    public function create()
    {
        $data = [

            'services' => $this->serviceModel

                ->where('status',1)

                ->findAll()

        ];

        return view('master/fuel_rules/create',$data);
    }

    public function store()
    {
        $this->fuelModel->save([

            'service_id'      => $this->request->getPost('service_id'),

            'customer_type'   => $this->request->getPost('customer_type'),

            'customer_value'  => $this->request->getPost('customer_value'),

            'vendor_type'     => $this->request->getPost('vendor_type'),

            'vendor_value'    => $this->request->getPost('vendor_value'),

            'effective_from'  => $this->request->getPost('effective_from'),

            'effective_to'    => $this->request->getPost('effective_to'),

            'status'          => $this->request->getPost('status')

        ]);

        return redirect()
            ->to(site_url('master/fuel-rules'))
            ->with('success','Fuel Rule berhasil disimpan.');
    }

    public function edit($id)
    {
        $data = [

            'rule' => $this->fuelModel->find($id),

            'services' => $this->serviceModel
                ->where('status',1)
                ->findAll()

        ];

        return view('master/fuel_rules/edit',$data);
    }

    public function update($id)
    {
        $this->fuelModel->update($id,[

            'service_id'      => $this->request->getPost('service_id'),

            'customer_type'   => $this->request->getPost('customer_type'),

            'customer_value'  => $this->request->getPost('customer_value'),

            'vendor_type'     => $this->request->getPost('vendor_type'),

            'vendor_value'    => $this->request->getPost('vendor_value'),

            'effective_from'  => $this->request->getPost('effective_from'),

            'effective_to'    => $this->request->getPost('effective_to'),

            'status'          => $this->request->getPost('status')

        ]);

        return redirect()
            ->to(site_url('master/fuel-rules'))
            ->with('success','Fuel Rule berhasil diupdate.');
    }

    public function delete($id)
    {
        $this->fuelModel->update($id,[

            'status'=>0

        ]);

        return redirect()
            ->to(site_url('master/fuel-rules'))
            ->with('success','Fuel Rule berhasil dinonaktifkan.');
    }

}