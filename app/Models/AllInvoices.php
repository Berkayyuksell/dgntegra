<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllInvoices extends Model
{   protected $connection = 'second_db';
    protected $table = 'AllInvoices';
}
