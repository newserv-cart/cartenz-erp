<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\ExpressChargeRuleModel;
use App\Models\CountryModel;
use App\Models\ServiceModel;

class ExpressChargeRuleController extends BaseController
{
    protected $ruleModel;
    protected $countryModel;
    protected $serviceModel;

    public function __construct()
    {
        $this->ruleModel     = new ExpressChargeRuleModel();
        $this->countryModel  = new CountryModel();
        $this->serviceModel  = new ServiceModel();
    }

    /**
     * Aturan validasi yang digunakan berulang
     */
    private function rules()
    {
        return [
            'country_id'                => 'required|integer',
            'service_id'                => 'required|integer',
            
            'customer_fuel_type'        => 'required|in_list[FIXED,PERCENT]',
            'customer_fuel_value'       => 'required|numeric',
            'vendor_fuel_type'          => 'required|in_list[FIXED,PERCENT]',
            'vendor_fuel_value'         => 'required|numeric',
            
            'customer_gogreen_type'     => 'required|in_list[FIXED,PER_KG]',
            'customer_gogreen_value'    => 'required|numeric',
            'vendor_gogreen_type'       => 'required|in_list[FIXED,PER_KG]',
            'vendor_gogreen_value'      => 'required|numeric',
            
            'customer_remote_type'      => 'required|in_list[FIXED,PERCENT]',
            'customer_remote_value'     => 'required|numeric',
            'vendor_remote_type'        => 'required|in_list[FIXED,PERCENT]',
            'vendor_remote_value'       => 'required|numeric',
            
            'effective_from'            => 'permit_empty|valid_date',
            'effective_to'              => 'permit_empty|valid_date',
            'status'                    => 'required|in_list[0,1]'
        ];
    }

    /**
     * Ambil dan rapikan data dari form
     * Menghilangkan duplikasi penulisan field
     */
    private function getFormData(): array
    {
        $data = $this->request->getPost();

        // Ubah tanggal kosong menjadi NULL
        $data['effective_from'] = !empty($data['effective_from']) ? $data['effective_from'] : null;
        $data['effective_to']   = !empty($data['effective_to'])   ? $data['effective_to']   : null;

        return $data;
    }

    /**
     * Validasi logika tanggal
     */
    private function validateDateRange(array $data): bool
    {
        if (!empty($data['effective_from']) && !empty($data['effective_to'])) {
            return strtotime($data['effective_from']) <= strtotime($data['effective_to']);
        }
        return true;
    }

    /**
     * Tampilkan daftar aturan dengan paginasi
     */
    public function index()
    {
        $data = [
            'title' => 'Express Charge Rules',
            'rules' => $this->ruleModel
                ->select('
    express_charge_rules.*,
    countries.country_name,
    services.service_name
')
                ->join('countries', 'countries.id = express_charge_rules.country_id', 'left')
                ->join('services', 'services.id = express_charge_rules.service_id', 'left')
                ->orderBy('express_charge_rules.id', 'DESC')
                ->paginate(20),
            'pager' => $this->ruleModel->pager
        ];

        return view('master/express_charge_rules/index', $data);
    }

    /**
     * Tampilkan halaman tambah
     */
    public function create()
    {
        $data = [
            'title'     => 'Add New Charge Rule',
            'countries' => $this->countryModel->where('status', 1)->findAll(),
            'services'  => $this->serviceModel->where('status', 1)->findAll()
        ];

        return view('master/express_charge_rules/create', $data);
    }

    /**
     * Simpan data baru
     */
    public function store()
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = $this->getFormData();

        // Cek rentang tanggal
        if (!$this->validateDateRange($data)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Effective From tidak boleh lebih besar dari Effective To.');
        }

        // Simpan cukup panggil sekali
        $this->ruleModel->save($data);

        return redirect()->to(site_url('master/express-charge-rules'))
            ->with('success', 'Charge rule created successfully.');
    }

    /**
     * Tampilkan halaman edit
     */
    public function edit($id)
    {
        $rule = $this->ruleModel->find($id);

        if (!$rule) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $data = [
            'title'     => 'Edit Charge Rule',
            'rule'      => $rule,
            'countries' => $this->countryModel->where('status', 1)->findAll(),
            'services'  => $this->serviceModel->where('status', 1)->findAll()
        ];

        return view('master/express_charge_rules/edit', $data);
    }

    /**
     * Perbarui data
     */
    public function update($id)
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = $this->getFormData();

        // Cek rentang tanggal
        if (!$this->validateDateRange($data)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Effective From tidak boleh lebih besar dari Effective To.');
        }

        // Update cukup panggil sekali
        $this->ruleModel->update($id, $data);

        return redirect()->to(site_url('master/express-charge-rules'))
            ->with('success', 'Charge rule updated successfully.');
    }

    
}