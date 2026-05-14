<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('vendor_id')->constrained();
            $table->string('po_number')->unique();
            $table->date('order_date');
            $table->date('expected_date')->nullable();
            $table->text('note')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, partially_received, received, cancelled
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
