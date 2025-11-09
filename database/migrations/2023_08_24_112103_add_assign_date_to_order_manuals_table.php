<?php

use App\Models\OrderManual;
use App\Models\ProductStockLog;
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
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->date('assign_date')->nullable()->after('deleted_at');
        });
        Schema::table('order_leads', function (Blueprint $table) {
            $table->date('assign_date')->nullable()->after('deleted_at');
        });

        // $stocks_logs = ProductStockLog::where('type_stock', 'out')->where('type_history', 'so')->where('type_product', 'variant')->groupBy('description')->get();
        // foreach ($stocks_logs as $key => $stock) {
        //     $uid_lead = explode(' - ', $stock->description)[1];
        //     if ($uid_lead) {
        //         $manual = OrderManual::where('uid_lead', $uid_lead)->first();
        //         if ($manual) {
        //             $manual->update(['assign_date' => $stock->created_at]);
        //         }
        //     }
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->dropColumn('assign_date');
        });
        Schema::table('order_leads', function (Blueprint $table) {
            $table->dropColumn('assign_date');
        });
    }
};
