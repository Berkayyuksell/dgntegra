<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EArchiveInvoicesOut extends Model
{
    protected $fillable = [
        'invoice_id',
        'uuid',
        'sender_name',
        'sender_identifier',
        'customer_name',
        'customer_identifier',
        'profile_id',
        'invoice_type',
        'earchive_type',
        'sending_type',
        'status',
        'status_code',
        'issue_date',
        'payable_amount',
        'taxable_amount',
        'currency_code',
        'company_code',
    ];

    public function V3Invoices()
    {
        return $this->hasMany(AllInvoices::class,'InvoiceHeaderID', 'uuid');
    }




}
