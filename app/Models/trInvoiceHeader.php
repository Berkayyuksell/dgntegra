<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class trInvoiceHeader extends Model
{
    protected $connection = 'second_db';
    protected $table = 'tr_invoice_header';
}
