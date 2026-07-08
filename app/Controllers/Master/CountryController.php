<?php

namespace App\Controllers\Master;

use App\Controllers\BaseController;
use App\Models\CountryModel;
use App\Models\RegionModel;
use App\Models\ExpressZoneModel;
use App\Models\GoGreenZoneModel;

class CountryController extends BaseController
{
    protected $countryModel;
    protected $regionModel;
    protected $currencyModel;
    protected $expressZoneModel;
    protected $goGreenZoneModel;

    public function __construct()
    {
        $this->countryModel      = new CountryModel();
        $this->regionModel       = new RegionModel();
        $this->currencyModel     = new CurrencyModel();
        $this->expressZoneModel  = new ExpressZoneModel();
        $this->goGreenZoneModel  = new GoGreenZoneModel();
    }

    /**
     * Country List
     */
    public function index()
    {
        $data = [

            'title' => 'Countries',

            'countries' => $this->countryModel
                ->select('countries.*,
                          regions.region_name,
                          currencies.currency_code,
                          express_zones.zone_code AS express_zone,
                          gogreen_zones.zone_code AS gogreen_zone')

                ->join('regions','regions.id=countries.region_id','left')

                
                ->join('express_zones','express_zones.id=countries.express_zone_id','left')

                ->join('gogreen_zones','gogreen_zones.id=countries.gogreen_zone_id','left')

                ->orderBy('country_name','ASC')

                ->paginate(20),

            'pager'=>$this->countryModel->pager

        ];

        return view('master/countries/index',$data);
    }

    /**
     * Create Form
     */
    public function create()
    {
        $data = [

            'regions' => $this->regionModel
                ->where('status',1)
                ->findAll(),

            
            'expressZones' => $this->expressZoneModel
                ->where('status',1)
                ->orderBy('zone_code')
                ->findAll(),

            'goGreenZones' => $this->goGreenZoneModel
                ->where('status',1)
                ->orderBy('zone_code')
                ->findAll(),

        ];

        return view('master/countries/create',$data);
    }

}