<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\CommodityChargeRuleModel;
use App\Models\CommodityTypeModel;

class CommodityChargeRuleController extends BaseController
{
    protected $ruleModel;
    protected $commodityModel;

    public function __construct()
    {
        $this->ruleModel = new CommodityChargeRuleModel();
        $this->commodityModel = new CommodityTypeModel();
    }

    // Tampilkan daftar aturan biaya komoditas
   public function index()
{
    $data['rules'] = $this->ruleModel
        ->select('commodity_charge_rules.*, commodity_types.commodity_name')
        ->join(
            'commodity_types',
            'commodity_types.id = commodity_charge_rules.commodity_type_id',
            'left'
        )
        ->findAll();

    $data['commodities'] = $this->commodityModel->findAll();

    return view('master/commodity_charge_rule/index', $data);
}

    // Buka halaman tambah aturan baru
    public function create()
{
    return view(
        'master/commodity_charge_rules/create',
        [
            'commodities'=>$this->commodityModel->findAll()
        ]
    );
}

    // Simpan data baru ke database
   public function store()
{
    $this->ruleModel->insert([

        'commodity_type_id'    => $this->request->getPost('commodity_type_id'),

        'customer_insurance'   => $this->request->getPost('customer_insurance'),
        'vendor_insurance'     => $this->request->getPost('vendor_insurance'),

        'customer_packing'     => $this->request->getPost('customer_packing'),
        'vendor_packing'       => $this->request->getPost('vendor_packing'),

        'customer_additional'  => $this->request->getPost('customer_additional'),
        'vendor_additional'    => $this->request->getPost('vendor_additional'),

        'status'               => 1

    ]);

    return redirect()
        ->to(site_url('master/commodity-charge-rules'))
        ->with('success','Commodity Charge Rule berhasil disimpan.');
}

    // Buka halaman ubah data
    public function edit($id)
    {
        $data['rule']        = $this->ruleModel->find($id);
        $data['commodities'] = $this->commodityModel->findAll();

        if (!$data['rule']) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        return view('master/commodity_charge_rule/edit', $data);
    }

    // Update data ke database
   public function update($id)
{
    $this->ruleModel->update($id,[

        'commodity_type_id'    => $this->request->getPost('commodity_type_id'),

        'customer_insurance'   => $this->request->getPost('customer_insurance'),
        'vendor_insurance'     => $this->request->getPost('vendor_insurance'),

        'customer_packing'     => $this->request->getPost('customer_packing'),
        'vendor_packing'       => $this->request->getPost('vendor_packing'),

        'customer_additional'  => $this->request->getPost('customer_additional'),
        'vendor_additional'    => $this->request->getPost('vendor_additional')

    ]);

    return redirect()
        ->to(site_url('master/commodity-charge-rules'))
        ->with('success','Commodity Charge Rule berhasil diperbarui.');
}

    // Hapus data
    public function delete($id)
    {
        $this->ruleModel->delete($id);
        return redirect()->to(site_url('master/commodity-charge-rule'))->with('success', 'Aturan biaya berhasil dihapus');
    }


public function getRule($commodityId)
{
    $rule = $this->ruleModel
        ->where('commodity_type_id', $commodityId)
        ->first();

    if (!$rule) {

        return $this->response->setJSON([
            'success' => false
        ]);

    }

    return $this->response->setJSON([

        'success' => true,

        'rule' => $rule

    ]);
}
}

