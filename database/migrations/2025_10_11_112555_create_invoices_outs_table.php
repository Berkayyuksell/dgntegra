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
        Schema::create('invoices_outs', function (Blueprint $table) {
            $table->id();
            // Attributes
            $table->string('external_id')->nullable(); // ID
            $table->string('list_id')->nullable(); // LIST_ID
            $table->uuid('uuid')->nullable(); // UUID

            // Header
            $table->string('sender')->nullable();
            $table->string('receiver')->nullable();
            $table->string('supplier')->nullable();
            $table->string('customer')->nullable();
            $table->dateTime('issue_date')->nullable();
            $table->decimal('payable_amount', 15, 2)->nullable();
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->string('profile_id')->nullable();
            $table->string('invoice_type_code')->nullable();
            $table->string('status')->nullable();
            $table->string('status_description')->nullable();
            $table->string('gib_status_code')->nullable();
            $table->string('gib_status_description')->nullable();
            $table->dateTime('cdate')->nullable();
            $table->uuid('envelope_identifier')->nullable();
            $table->string('status_code')->nullable();
            $table->decimal('line_extension_amount', 15, 2)->nullable();
            $table->decimal('tax_exclusive_total_amount', 15, 2)->nullable();
            $table->decimal('tax_inclusive_total_amount', 15, 2)->nullable();
            $table->decimal('allowance_total_amount', 15, 2)->nullable();

            $table->string('company_code')->nullable();

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('invoices_outs');
    }
};
