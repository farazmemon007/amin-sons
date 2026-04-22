<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vouchers')) {
            Schema::create('vouchers', function (Blueprint $table) {
                $table->id();
                $table->enum('type', ['receipt', 'payment'])->comment('receipt = incoming, payment = outgoing');
                $table->unsignedBigInteger('from_branch_id');
                $table->unsignedBigInteger('to_branch_id')->nullable()->comment('For inter-branch, NULL for external');
                $table->decimal('amount', 12, 2);
                $table->enum('method', ['cash', 'bank', 'cheque'])->default('cash');
                $table->string('reference')->nullable()->comment('Cheque no, bank ref, etc');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
                
                // Foreign keys
                $table->foreign('from_branch_id')->references('id')->on('branches')->onDelete('cascade');
                $table->foreign('to_branch_id')->references('id')->on('branches')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                
                $table->index('from_branch_id');
                $table->index('to_branch_id');
                $table->index('type');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
