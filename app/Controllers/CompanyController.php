<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CompanyModel;

class CompanyController extends BaseController
{
    public function index()
    {
        $company = (new CompanyModel())->find(1);

        return view('company/index', [
            'company' => $company
        ]);
    }

    public function update()
    {
        $model = new CompanyModel();

        $model->update(1, [

            'company_name' => $this->request->getPost('company_name'),
            'legal_name'   => $this->request->getPost('legal_name'),
            'address'      => $this->request->getPost('address'),
            'city'         => $this->request->getPost('city'),
            'country'      => $this->request->getPost('country'),
            'phone'        => $this->request->getPost('phone'),
            'email'        => $this->request->getPost('email'),
            'website'      => $this->request->getPost('website'),
            'tax_name'     => $this->request->getPost('tax_name'),
            'tax_number'   => $this->request->getPost('tax_number'),
            'vat_number'   => $this->request->getPost('vat_number'),
            'invoice_notes'=> $this->request->getPost('invoice_notes'),

        ]);

        return redirect()->to('/company')
            ->with('success','Company profile updated');
    }
}