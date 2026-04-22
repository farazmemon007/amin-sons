<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('order_id')->index();
            $table->string('billty_no')->nullable()->after('issued_by');
            $table->date('billty_date')->nullable()->after('billty_no');
            $table->string('transporter')->nullable()->after('billty_date');
            $table->decimal('billty_amount', 14, 2)->nullable()->after('transporter');
            $table->string('prepared_by')->nullable()->after('billty_amount');
        });
    }

    public function down()
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            $table->dropColumn(['warehouse_id','billty_no','billty_date','transporter','billty_amount','prepared_by']);
        });
    }
};
