<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productbookings', function (Blueprint $table) {
            if (!Schema::hasColumn('productbookings', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('customer_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productbookings', function (Blueprint $table) {
            if (Schema::hasColumn('productbookings', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
        });
    }
};
