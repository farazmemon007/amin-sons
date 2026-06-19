<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complaint_no')->unique();                         // CMP-2026-KHI-00001
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->enum('scenario_type', ['walk_in', 'remote', 'home_service'])->default('walk_in');

            // Customer Info
            $table->unsignedBigInteger('customer_id')->nullable();            // Existing customer
            $table->string('customer_name')->nullable();                      // Manual name
            $table->string('customer_mobile')->nullable();
            $table->text('customer_address')->nullable();

            // Product Info
            $table->unsignedBigInteger('product_id')->nullable();             // From products table
            $table->string('product_name')->nullable();                       // Manual product name
            $table->string('product_serial')->nullable();                     // Serial number
            $table->string('product_model')->nullable();                      // Model number

            // Complaint Details
            $table->text('issue_description');
            $table->date('complaint_date');
            $table->string('photo_path')->nullable();                         // For remote complaints

            // Status
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'closed'])->default('pending');
            $table->enum('resolution_type', ['exchanged', 'repaired', 'refunded', 'pending_stock', 'none'])->default('none')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->date('resolved_date')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();

            // Barcode
            $table->string('barcode_path')->nullable();

            // Meta
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('resolved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
