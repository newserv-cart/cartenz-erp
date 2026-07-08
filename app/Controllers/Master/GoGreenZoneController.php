<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\GoGreenZoneModel;

class GoGreenZoneController extends BaseController
{

    protected $model;

    public function __construct()
    {
        $this->model=new GoGreenZoneModel();
    }

    public function index()
    {
        $data['zones']=$this->model
            ->orderBy('zone_code')
            ->findAll();

        return view('master/gogreen_zones/index',$data);
    }

    public function create()
    {
        return view('master/gogreen_zones/create');
    }

    public function store()
    {

        $this->model->save([

            'zone_code'=>$this->request->getPost('zone_code'),

            'zone_name'=>$this->request->getPost('zone_name'),

            'description'=>$this->request->getPost('description'),

            'status'=>$this->request->getPost('status')

        ]);

        return redirect()->to(site_url('master/gogreen-zones'));

    }


    public function edit($id)
{
    $data['zone'] = $this->model->find($id);

    if (!$data['zone']) {
        return redirect()->to(site_url('master/gogreen-zones'))
            ->with('error', 'Zone tidak ditemukan.');
    }

    return view('master/gogreen_zones/edit', $data);
}

public function update($id)
{
    $this->model->update($id, [

        'zone_code'   => $this->request->getPost('zone_code'),
        'zone_name'   => $this->request->getPost('zone_name'),
        'description' => $this->request->getPost('description'),
        'status'      => $this->request->getPost('status'),

    ]);

    return redirect()->to(site_url('master/gogreen-zones'))
        ->with('success', 'Go Green Zone berhasil diupdate.');
}

public function delete($id)
{
    $this->model->update($id, [

        'status' => 0

    ]);

    return redirect()->to(site_url('master/gogreen-zones'))
        ->with('success', 'Go Green Zone berhasil dinonaktifkan.');
}




}