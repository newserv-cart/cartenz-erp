<?php

namespace App\Models;

use CodeIgniter\Model;

class AwbSequenceModel extends Model
{
    protected $table            = 'awb_sequences';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
                                    'awb_prefix',
                                    'awb_year',
                                    'awb_month',
                                    'last_number'
                                    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';


    


}
