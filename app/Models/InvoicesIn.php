<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicesIn extends Model
{
    protected $fillable = [
        'external_id',
        'list_id',
        'uuid',
        'sender',
        'receiver',
        'supplier',
        'customer',
        'issue_date',
        'payable_amount',
        'from_address',
        'to_address',
        'profile_id',
        'invoice_type_code',
        'status',
        'status_description',
        'gib_status_code',
        'gib_status_description',
        'cdate',
        'envelope_identifier',
        'status_code',
        'line_extension_amount',
        'tax_exclusive_total_amount',
        'tax_inclusive_total_amount',
        'allowance_total_amount',
        'company_code',
    ];


    public function V3Invoices()
    {
        return $this->hasMany(AllInvoices::class,'InvoiceHeaderID', 'uuid');
    }

    public function V3InvoicesIn()
    {
        return $this->hasMany(e_InboxInvoiceHeader::class,'InvoiceHeaderID', 'uuid');
    }



}
