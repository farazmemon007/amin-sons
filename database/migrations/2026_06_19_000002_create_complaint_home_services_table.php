<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_home_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('complaint_id');
            $table->string('technician_name');
            $table->date('visit_date');
            $table->string('visit_time')->nullable();
            $table->decimal('visiting_charges', 10, 2)->default(0);
            $table->boolean('charges_paid')->default(false);
            $table->text('visit_notes')->nullable();
            $table->enum('visit_status', ['scheduled', 'visited', 'resolved', 'follow_up'])->default('scheduled');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('complaint_id')->references('id')->on('complaints')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_home_services');
    }
};
