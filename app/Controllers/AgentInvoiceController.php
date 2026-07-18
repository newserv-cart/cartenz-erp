<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class AgentInvoiceController extends BaseController
{
    public function index()
    {
        return redirect()->to(site_url('agent/self-billing'));
    }

    public function detail($id)
    {
        return redirect()->to(site_url('agent/self-billing/invoice/' . (int) $id));
    }
}
