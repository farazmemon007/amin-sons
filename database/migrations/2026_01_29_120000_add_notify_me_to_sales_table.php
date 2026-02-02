<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('productbookings')) return;

        Schema::table('productbookings', function (Blueprint $table) {
            if (! Schema::hasColumn('productbookings', 'notify_me')) {
                $table->integer('notify_me')->nullable()->after('final_balance2');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('productbookings')) return;

        Schema::table('productbookings', function (Blueprint $table) {
            if (Schema::hasColumn('productbookings', 'notify_me')) {
                $table->dropColumn('notify_me');
            }
        });
    }
};
