<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('e_archive_invoices_outs', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->nullable();
            $table->uuid('uuid')->unique();
            $table->string('sender_name')->nullable();
            $table->string('sender_identifier')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_identifier')->nullable();
            $table->string('profile_id')->nullable();
            $table->string('invoice_type')->nullable();
            $table->string('earchive_type')->nullable();
            $table->string('sending_type')->nullable();
            $table->string('status')->nullable();
            $table->string('status_code')->nullable();
            $table->dateTime('issue_date')->nullable();
            $table->decimal('payable_amount', 12, 2)->nullable();
            $table->decimal('taxable_amount', 12, 2)->nullable();
            $table->string('currency_code', 5)->default('TRY');

            $table->string('company_code')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_archive_invoices_outs');
    }
};
