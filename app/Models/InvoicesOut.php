<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InvoicesOut extends Model
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


    public function V3InvoiceHeader()
    {
        return $this->hasMany(AllInvoices::class,'InvoiceHeaderID', 'uuid');
    }

    public function V3trInvoiceHeader(){
        return $this->hasMany(TrInvoiceHeader::class,'InvoiceHeaderID', 'uuid');
    }

    protected static function booted()
    {
        static::addGlobalScope('last_month', function (Builder $builder) {
            $oneMonthAgo = Carbon::now()->subMonth()->startOfDay();
            $builder->where('issue_date', '>=', $oneMonthAgo);
        });
    }


}
