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
        if (!Schema::hasTable('sale_postings')) {
            Schema::create('sale_postings', function (Blueprint $table) {
                $table->id();
                
                // Foreign keys
                $table->unsignedBigInteger('sale_id')->index();
                $table->unsignedBigInteger('product_id')->index();
                
                // Quantity
                $table->integer('qty')->default(0);
                
                // Source information
                $table->enum('source_type', ['branch', 'warehouse'])->default('branch');
                $table->unsignedBigInteger('source_id')->nullable();
                $table->index(['source_type', 'source_id']);
                
                // Status for draft/pending/processed workflow
                $table->enum('status', ['pending', 'processed'])->default('pending');
                
                $table->timestamps();
                
                // Foreign key constraints
                $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        } else {
            // Table exists - validate and add missing columns
            Schema::table('sale_postings', function (Blueprint $table) {
                if (!Schema::hasColumn('sale_postings', 'sale_id')) {
                    $table->unsignedBigInteger('sale_id')->index();
                }
                if (!Schema::hasColumn('sale_postings', 'product_id')) {
                    $table->unsignedBigInteger('product_id')->index();
                }
                if (!Schema::hasColumn('sale_postings', 'qty')) {
                    $table->integer('qty')->default(0);
                }
                if (!Schema::hasColumn('sale_postings', 'source_type')) {
                    $table->enum('source_type', ['branch', 'warehouse'])->default('branch');
                }
                if (!Schema::hasColumn('sale_postings', 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable();
                }
                if (!Schema::hasColumn('sale_postings', 'status')) {
                    $table->enum('status', ['pending', 'processed'])->default('pending');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_postings');
    }
};
