<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\GoGreenRuleModel;
use App\Models\ServiceModel;
use App\Models\GoGreenZoneModel;

class GoGreenRuleController extends BaseController
{
    protected $ruleModel;
    protected $serviceModel;
    protected $zoneModel;

    public function __construct()
    {
        $this->ruleModel    = new GoGreenRuleModel();
        $this->serviceModel = new ServiceModel();
        $this->zoneModel    = new GoGreenZoneModel();
    }

    public function index()
    {
        $data = [

            'title' => 'Go Green Rules',

            'rules' => $this->ruleModel

                ->select('gogreen_rules.*,
                          services.service_name,
                          gogreen_zones.zone_code,
                          gogreen_zones.zone_name')

                ->join('services',
                    'services.id=gogreen_rules.service_id',
                    'left')

                ->join('gogreen_zones',
                    'gogreen_zones.id=gogreen_rules.gogreen_zone_id',
                    'left')

                ->orderBy('zone_code','ASC')

                ->paginate(20),

            'pager'=>$this->ruleModel->pager

        ];

        return view('master/gogreen_rules/index',$data);
    }

    public function create()
    {
        $data=[

            'services'=>$this->serviceModel
                ->where('status',1)
                ->findAll(),

            'zones'=>$this->zoneModel
                ->where('status',1)
                ->orderBy('zone_code')
                ->findAll()

        ];

        return view('master/gogreen_rules/create',$data);
    }

    public function store()
    {
        $this->ruleModel->save([

            'service_id'=>$this->request->getPost('service_id'),

            'gogreen_zone_id'=>$this->request->getPost('gogreen_zone_id'),

            'customer_type'=>$this->request->getPost('customer_type'),

            'customer_value'=>$this->request->getPost('customer_value'),

            'vendor_type'=>$this->request->getPost('vendor_type'),

            'vendor_value'=>$this->request->getPost('vendor_value'),

            'effective_from'=>$this->request->getPost('effective_from'),

            'effective_to'=>$this->request->getPost('effective_to'),

            'status'=>$this->request->getPost('status')

        ]);

        return redirect()->to(site_url('master/gogreen-rules'))
            ->with('success','Go Green Rule berhasil disimpan.');

    }

    public function edit($id)
    {
        $data=[

            'rule'=>$this->ruleModel->find($id),

            'services'=>$this->serviceModel
                ->where('status',1)
                ->findAll(),

            'zones'=>$this->zoneModel
                ->where('status',1)
                ->orderBy('zone_code')
                ->findAll()

        ];

        return view('master/gogreen_rules/edit',$data);
    }

    public function update($id)
    {
        $this->ruleModel->update($id,[

            'service_id'=>$this->request->getPost('service_id'),

            'gogreen_zone_id'=>$this->request->getPost('gogreen_zone_id'),

            'customer_type'=>$this->request->getPost('customer_type'),

            'customer_value'=>$this->request->getPost('customer_value'),

            'vendor_type'=>$this->request->getPost('vendor_type'),

            'vendor_value'=>$this->request->getPost('vendor_value'),

            'effective_from'=>$this->request->getPost('effective_from'),

            'effective_to'=>$this->request->getPost('effective_to'),

            'status'=>$this->request->getPost('status')

        ]);

        return redirect()->to(site_url('master/gogreen-rules'))
            ->with('success','Go Green Rule berhasil diupdate.');

    }

    public function delete($id)
    {
        $this->ruleModel->update($id,[

            'status'=>0

        ]);

        return redirect()->to(site_url('master/gogreen-rules'))
            ->with('success','Go Green Rule berhasil dinonaktifkan.');

    }

}