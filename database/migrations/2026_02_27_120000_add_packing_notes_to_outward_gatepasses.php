<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            $table->text('packing_notes')->nullable()->after('remarks');
        });
    }

    public function down()
    {
        Schema::table('outward_gatepasses', function (Blueprint $table) {
            $table->dropColumn('packing_notes');
        });
    }
};
