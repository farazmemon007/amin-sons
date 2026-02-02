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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Relationship fields
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            
            // Notification details
            $table->string('type')->comment('booking_payment, sale_payment, etc'); // e.g., 'booking_payment', 'sale_payment'
            $table->string('title')->comment('Notification title');
            $table->text('description')->nullable()->comment('Detailed message');
            
            // Dates
            $table->date('notification_date')->comment('When notification should trigger'); // Triggered date
            $table->dateTime('sent_at')->nullable()->comment('When it was actually sent');
            
            // Status
            $table->enum('status', ['pending', 'sent', 'dismissed'])->default('pending');
            $table->boolean('is_read')->default(false);
            
            // User tracking
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('booking_id')->references('id')->on('productbookings')->onDelete('cascade');
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for faster queries
            $table->index('notification_date');
            $table->index('status');
            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
