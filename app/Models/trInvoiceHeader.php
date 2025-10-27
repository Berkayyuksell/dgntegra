<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class trInvoiceHeader extends Model
{
    protected $table = 'trInvoiceHeader';





    protected static function booted()
    {
        static::addGlobalScope('last_month', function (Builder $builder) {
            $oneMonthAgo = Carbon::now()->subMonth()->startOfDay();
            $builder->where('InvoiceDate', '>=', $oneMonthAgo);
        });
    }


    public function V3AllInvoices(){
        return $this->hasMany(AllInvoices::class,'InvoiceHeaderID', 'InvoiceHeaderID');
    }

    public function V3ArchiveInvoices(){
        return $this->hasOne(EArchiveInvoicesOut::class,'uuid', 'InvoiceHeaderID');
    }

    public function V3OutInvoices(){
        return $this->hasOne(InvoicesOut::class,'uuid', 'InvoiceHeaderID');
    }





}


