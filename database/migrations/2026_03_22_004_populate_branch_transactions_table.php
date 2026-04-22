<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->comment('Branch whose ledger this is');
            $table->unsignedBigInteger('related_branch_id')->nullable()->comment('Other branch in transaction');
            $table->enum('type', ['debit', 'credit'])->comment('Debit = payable, Credit = receivable');
            $table->decimal('amount', 12, 2);
            $table->string('reference_type')->comment('transfer, payment, receipt, voucher');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('related_branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['branch_id', 'created_at']);
            $table->index(['related_branch_id', 'created_at']);
            $table->index('reference_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_transactions');
    }
};
