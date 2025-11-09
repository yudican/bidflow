<?php

use App\Models\InventoryDetailItem;
use App\Models\InventoryItem;
use App\Models\InventoryProductReturn;
use App\Models\InventoryProductStock;
use App\Models\LeadMaster;
use App\Models\OrderDeposit;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\ProductStock;
use App\Models\ProductVariantStock;
use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('lead_masters', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('order_leads', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('inventory_product_stocks', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('inventory_product_returns', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('order_deposits', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('product_variant_stocks', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('inventory_detail_items', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->softDeletes()->after('created_at');
        });

        // try {
        //     DB::beginTransaction();
        //     OrderManual::where('status', '!=', 1)->where('status', '!=', 2)->update(['deleted_at' => Carbon::now()]);
        //     OrderLead::where('status', '!=', 1)->where('status', '!=', 2)->update(['deleted_at' => Carbon::now()]);
        //     LeadMaster::where('status', '!=', 1)->update(['deleted_at' => Carbon::now()]);
        //     PurchaseOrder::where('status', '!=', 1)->where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);
        //     InventoryProductStock::where('inventory_status', '!=', 'received')->where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);
        //     InventoryProductReturn::where('status', '!=', 2)->where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);
        //     OrderDeposit::where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);
        //     ProductStock::where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);
        //     ProductVariantStock::where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);
        //     InventoryDetailItem::where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);
        //     InventoryItem::where('created_at', '<', '2023-08-20')->update(['deleted_at' => Carbon::now()]);

        //     DB::commit();
        // } catch (\Throwable $th) {
        //     //throw $th;
        //     DB::rollBack();
        // }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_masters', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('order_leads', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('order_manuals', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('inventory_product_stocks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('inventory_product_returns', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('order_deposits', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('product_variant_stocks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('inventory_detail_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
