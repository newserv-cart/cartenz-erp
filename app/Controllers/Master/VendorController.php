<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\VendorModel;

class VendorController extends BaseController
{
    protected $vendorModel;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Vendor Management',
            'vendors' => $this->vendorModel
                ->orderBy('vendor_name', 'ASC')
                ->paginate(20),
            'pager' => $this->vendorModel->pager
        ];

        return view('master/vendors/index', $data);
    }

    public function create()
    {
        return view('master/vendors/create');
    }

    public function store()
    {
        $this->vendorModel->save([

            'vendor_code'     => strtoupper($this->request->getPost('vendor_code')),
            'vendor_name'     => $this->request->getPost('vendor_name'),
            'contact_person'  => $this->request->getPost('contact_person'),
            'phone'           => $this->request->getPost('phone'),
            'email'           => $this->request->getPost('email'),
            'api_type'        => $this->request->getPost('api_type'),
            'api_url'         => $this->request->getPost('api_url'),
            'status'          => $this->request->getPost('status')

        ]);

        return redirect()
            ->to(site_url('master/vendors'))
            ->with('success','Vendor berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $data = [
            'vendor' => $this->vendorModel->find($id)
        ];

        return view('master/vendors/edit', $data);
    }

    public function update($id)
    {
        $this->vendorModel->update($id,[

            'vendor_code'     => strtoupper($this->request->getPost('vendor_code')),
            'vendor_name'     => $this->request->getPost('vendor_name'),
            'contact_person'  => $this->request->getPost('contact_person'),
            'phone'           => $this->request->getPost('phone'),
            'email'           => $this->request->getPost('email'),
            'api_type'        => $this->request->getPost('api_type'),
            'api_url'         => $this->request->getPost('api_url'),
            'status'          => $this->request->getPost('status')

        ]);

        return redirect()
            ->to(site_url('master/vendors'))
            ->with('success','Vendor berhasil diupdate.');
    }

    public function delete($id)
    {
        $this->vendorModel->update($id,[
            'status'=>0
        ]);

        return redirect()
            ->to(site_url('master/vendors'))
            ->with('success','Vendor berhasil dinonaktifkan.');
    }
}