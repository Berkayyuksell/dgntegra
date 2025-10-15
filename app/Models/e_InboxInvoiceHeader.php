<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class e_InboxInvoiceHeader extends Model
{
    protected $connection = "second_db";
    protected $table = "e_InboxInvoiceHeader";
}
