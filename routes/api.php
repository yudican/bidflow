<?php

use App\Http\Controllers\Api\AjaxController;
use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\Transaction\ConfirmPaymentController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\V1\ActivityController;
use App\Http\Controllers\Api\V1\ContactController as V1ContactController;
use App\Http\Controllers\Api\V1\Prospect\ProspectController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Notification\PaymentNotification;
use App\Http\Controllers\SendEmailController;
use App\Http\Controllers\Spa\AgentDomainManagementController;
use App\Http\Controllers\Spa\AgentManagementController;
use App\Http\Controllers\Spa\Auth\LoginController;
use App\Http\Controllers\Spa\Case\ManualController;
use App\Http\Controllers\Spa\Case\RefundController;
use App\Http\Controllers\Spa\Case\ReturnController;
use App\Http\Controllers\Spa\CheckoutAgent;
use App\Http\Controllers\Spa\ContactController;
use App\Http\Controllers\Spa\DashboardController;
use App\Http\Controllers\Spa\GeneralController;
use App\Http\Controllers\Spa\GenieController;
use App\Http\Controllers\Spa\MpethixController;
use App\Http\Controllers\Spa\GPCustomerController;
use App\Http\Controllers\Spa\GPSubmissionController;
use App\Http\Controllers\Spa\InventoryController;
use App\Http\Controllers\Spa\LeadController;
use App\Http\Controllers\Spa\Master\BannerController;
use App\Http\Controllers\Spa\Master\BrandController;
use App\Http\Controllers\Spa\Master\UrlShortenerController;
use App\Http\Controllers\Spa\Master\RedirectController;
use App\Http\Controllers\Spa\Master\NotifController;
use App\Http\Controllers\Spa\Master\CategoryCaseController;
use App\Http\Controllers\Spa\Master\CategoryController;
use App\Http\Controllers\Spa\Master\CompanyAccountController;
use App\Http\Controllers\Spa\Master\ProductCartonController;
use App\Http\Controllers\Spa\Master\LevelController;
use App\Http\Controllers\Spa\Master\LogisticController;
use App\Http\Controllers\Spa\Master\MasterDiscountController;
use App\Http\Controllers\Spa\Master\MasterPointController;
use App\Http\Controllers\Spa\Master\PackageController;
use App\Http\Controllers\Spa\Master\PaymentMethodController;
use App\Http\Controllers\Spa\Master\VariantController;
use App\Http\Controllers\Spa\Master\VoucherController;
use App\Http\Controllers\Spa\Master\PaymentTermController;
use App\Http\Controllers\Spa\Master\MasterTaxController;
use App\Http\Controllers\Spa\Master\PriorityCaseController;
use App\Http\Controllers\Spa\Master\ProductAdditionalController;
use App\Http\Controllers\Spa\Master\SalesChannelController;
use App\Http\Controllers\Spa\Master\SkuController;
use App\Http\Controllers\Spa\Master\SourceCaseController;
use App\Http\Controllers\Spa\Master\StatusCaseController;
use App\Http\Controllers\Spa\Master\TypeCaseController;
use App\Http\Controllers\Spa\Master\WarehouseController;
use App\Http\Controllers\Spa\Master\VendorController;
use App\Http\Controllers\Spa\MasterController;
use App\Http\Controllers\Spa\MenuController;
use App\Http\Controllers\Spa\Order\GpController;
use App\Http\Controllers\Spa\Order\OrderFreeBiesController;
use App\Http\Controllers\Spa\Order\SalesReturnController;
use App\Http\Controllers\Spa\OrderLeadController;
use App\Http\Controllers\Spa\TicketController;
use App\Http\Controllers\Spa\OrderManualController;
use App\Http\Controllers\Spa\ProductManagement\ConvertController;
use App\Http\Controllers\Spa\ProductManagement\ImportController;
use App\Http\Controllers\Spa\ProductManagement\ProductCommentRatingController;
use App\Http\Controllers\Spa\ProductManagement\ProductMarginBottom;
use App\Http\Controllers\Spa\ProductManagement\ProductMasterController;
use App\Http\Controllers\Spa\ProductManagement\ProductVariantController;
use App\Http\Controllers\Spa\Purchase\PurchaseOrderController;
use App\Http\Controllers\Spa\Purchase\PurchaseOrderAccurateController;
use App\Http\Controllers\Spa\Purchase\PurchaseRequisitionController;
use App\Http\Controllers\Spa\Setting\NotificationTemplateController;
use App\Http\Controllers\Spa\TransactionController;
use App\Http\Controllers\Spa\TransAgentController;
use App\Http\Controllers\Spa\AgentLocationController;
use App\Http\Controllers\Spa\BinController;
use App\Http\Controllers\Spa\CekQueryController;
use App\Http\Controllers\Spa\CommisionWithdrawController;
use App\Http\Controllers\Spa\ContactGroupController;
use App\Http\Controllers\Spa\Marketplace\MarketPlaceController;
use App\Http\Controllers\Spa\Master\CheckbookController;
use App\Http\Controllers\Spa\Master\MasterBatchIDController;
use App\Http\Controllers\Spa\Master\MasterBinController;
use App\Http\Controllers\Spa\Master\MasterOngkirController;
use App\Http\Controllers\Spa\Master\MasterPphController;
use App\Http\Controllers\Spa\Master\MasterSiteIDController;
use App\Http\Controllers\Spa\Master\RateLimitSettingController;
use App\Http\Controllers\Spa\Order\OrderInvoiceController;
use App\Http\Controllers\Spa\Order\OrderKonsinyasiController;
use App\Http\Controllers\Spa\Purchase\PurchaseInvoiceEntryController;
use App\Http\Controllers\Webhook\GineeWebhookController;
use App\Http\Controllers\Spa\StockMovementController;
use App\Http\Controllers\Spa\BarcodeController;
use App\Http\Controllers\Spa\Order\SalesOrderController;
use App\Http\Controllers\SystemStatusController;
use App\Http\Controllers\Spa\AssetController;
use App\Http\Controllers\Spa\UserManagement\RoleController;
use App\Http\Controllers\Spa\Accurate\AccurateController;
use App\Models\OrderManual;
use App\Models\ProspectActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Spa\Accurate\AccurateActualStocksController;
use App\Http\Controllers\Spa\Accurate\AccurateCustomerController;
use App\Http\Controllers\Spa\Accurate\AccurateItemsController;
use App\Http\Controllers\Spa\Accurate\AccurateStockCountController;
use App\Http\Controllers\Spa\Accurate\SalesOrderController as AccurateSalesOrderController;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| SPA API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// API Authentication Routes
Route::prefix('auth')->group(function () {
    // Public routes
    Route::post('login', [ApiAuthController::class, 'login'])->name('api.auth.login');
    
    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [ApiAuthController::class, 'logout'])->name('api.auth.logout');
        Route::get('me', [ApiAuthController::class, 'me'])->name('api.auth.me');
        Route::post('refresh', [ApiAuthController::class, 'refresh'])->name('api.auth.refresh');
        Route::post('revoke-all', [ApiAuthController::class, 'revokeAll'])->name('api.auth.revoke-all');
    });
});
// agent location
Route::get('domain', [AgentLocationController::class, 'listAgentDomain']);
Route::get('district/{prov_id}', [AgentLocationController::class, 'listDistrictByProvince']);
Route::get('subdistrict/{district_id}', [AgentLocationController::class, 'listSubdistrictByDistrict']);
Route::get('user/province', [AgentLocationController::class, 'listProvinceByUser']);
Route::get('user/subdistrict/{subdistrict_id}', [AgentLocationController::class, 'listUserBySubdistrict']);


Route::get('system/_status', [SystemStatusController::class, 'getCommitStatus']);
Route::post('system/_update', [SystemStatusController::class, 'deleteUpdateFile']);

Route::post('proccess/login', [LoginController::class, 'login']);

// products
Route::prefix('product-management')->group(function () {
    Route::post('products', [ProductMasterController::class, 'listProductMaster']);
    Route::post('product-variants', [ProductVariantController::class, 'listProductVariant']);
    Route::get('product-with-sku', [ProductVariantController::class, 'productWithSku']);
});


// warehouse
Route::get('warehouse/list', [WarehouseController::class, 'getWarehouseList']);

// purchase request
Route::get('master/payment-method', [PaymentMethodController::class, 'listPaymentMethod']);
Route::get('general/approval-user', [GeneralController::class, 'getApprovalUser']);
Route::get('general/purchasing-user', [GeneralController::class, 'getPurchasingUser']);
Route::get('general/perlengkapan', [GeneralController::class, 'loadPerlengkapan']);
Route::post('purchase-request/save', [PurchaseRequisitionController::class, 'createRequitition']);


Route::get('warehouse/list', [WarehouseController::class, 'getWarehouseList']);
Route::get('vendor/list', [VendorController::class, 'getVendorList']);

Route::group(['prefix' => 'accurate'], function () {
    // Statistics
    Route::get('/statistics', [AccurateController::class, 'getStatistics']);

    // Activities Management
    Route::get('/activities', [AccurateController::class, 'getActivities']);
    Route::post('/activities', [AccurateController::class, 'createActivity']);
    Route::get('/activities/summary', [AccurateController::class, 'getActivitySummary']);


    // Customer Management
    Route::get('/customers', [AccurateCustomerController::class, 'getCustomers']);
    Route::get('/customer-subaccounts', [AccurateCustomerController::class, 'getCustomerSubaccounts']);
    // Items Management
    Route::get('/items', [AccurateItemsController::class, 'getItems']);
    Route::get('/items/{id}', [AccurateItemsController::class, 'getItemDetail']);
    Route::get('/items/category/{category}', [AccurateItemsController::class, 'getItemsByCategory']);
    Route::get('/items/categories/list', [AccurateItemsController::class, 'getItemCategories']);
    Route::get('/items/stock/list', [AccurateItemsController::class, 'getItemsWithStock']);

    // Stock Count Management
    Route::get('/stock-count', [AccurateStockCountController::class, 'getStockCounts']);
    Route::get('/stock-count/{id}', [AccurateStockCountController::class, 'getStockCountDetail']);
    Route::post('/stock-count', [AccurateStockCountController::class, 'createStockCount']);
    Route::put('/stock-count/{id}', [AccurateStockCountController::class, 'updateStockCount']);
    Route::delete('/stock-count/{id}', [AccurateStockCountController::class, 'deleteStockCount']);
    Route::get('/stock-count/summary/report', [AccurateStockCountController::class, 'getStockCountSummary']);
    Route::get('/stock-count/count/{countId}', [AccurateStockCountController::class, 'getStockCountByCountId']);
    Route::post('/stock-count/bulk', [AccurateStockCountController::class, 'bulkCreateStockCounts']);


    // Stock Count Attachments
    Route::get('/stock-count/{id}/attachments', [AccurateStockCountController::class, 'getAttachmentUrls']);
    Route::post('/stock-count/{id}/attachments', [AccurateStockCountController::class, 'uploadAttachments']);
    Route::get('/stock-count/{id}/attachment/{filename}', [AccurateStockCountController::class, 'downloadAttachment']);

    // Sales Order Management
    Route::get('/sales-orders', [AccurateSalesOrderController::class, 'getSalesOrders']);
    Route::get('/sales-orders/{id}', [AccurateSalesOrderController::class, 'getSalesOrderDetail']);
    Route::post('/sales-orders', [AccurateSalesOrderController::class, 'createSalesOrder']);
    Route::put('/sales-orders/{id}', [AccurateSalesOrderController::class, 'updateSalesOrder']);
    Route::delete('/sales-orders/{id}', [AccurateSalesOrderController::class, 'deleteSalesOrder']);
    Route::put('/sales-orders/{id}/status', [AccurateSalesOrderController::class, 'updateStatus']);

    Route::get('master/payment-term', [PaymentTermController::class, 'listPaymentTerm']);

    Route::get('/last-sync-customer', function () {
        $lastSync = DB::connection('pgsql')->table('accurate_sync_logs')
            ->where('type', 'customer')
            ->orderByDesc('sync_date')
            ->first();

        return response()->json([
            'status' => 'success',
            'last_synced_at' => $lastSync?->sync_date,
        ]);
    });

    Route::get('/last-sync-product', function () {
        $lastSync = DB::connection('pgsql')->table('accurate_sync_logs')
            ->where('type', 'product')
            ->orderByDesc('sync_date')
            ->first();

        return response()->json([
            'status' => 'success',
            'last_synced_at' => $lastSync?->sync_date,
        ]);
    });
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    // general
    Route::get('general/load-user', [GeneralController::class, 'loadUser']);
    Route::get('general/load-user-menu', [GeneralController::class, 'loadUserMenu']);
    Route::post('general/store-setting', [GeneralController::class, 'storeSetting']);
    Route::post('general/load-setting', [GeneralController::class, 'loadSetting']);
    Route::post('general/delete-setting', [GeneralController::class, 'deleteSetting']);
    Route::post('general/search-contact', [GeneralController::class, 'getContact']);
    Route::post('general/asset/contact-owner', [GeneralController::class, 'getContactOwner']);
    Route::post('general/search-sales', [GeneralController::class, 'getSales']);
    Route::post('general/search-contact-warehouse', [GeneralController::class, 'getContactWarehouse']);
    Route::post('general/search-contact-ahligizi', [GeneralController::class, 'getContactAhliGizi']);
    Route::post('general/search-company', [GeneralController::class, 'getCompany']);
    Route::post('general/search-telmark-user', [GeneralController::class, 'getTelmarkUserCreated']);
    Route::get('general/warehouse-user', [GeneralController::class, 'getWarehouseUser']);
    Route::post('general/approval-user', [GeneralController::class, 'getApprovalUser']);
    Route::post('general/purchasing-user', [GeneralController::class, 'getPurchasingUser']);
    Route::get('general/address-user/{user_id}', [GeneralController::class, 'getAddressUser']);
    Route::get('general/user-with-address/{user_id}', [GeneralController::class, 'getAddressWithUser']);
    Route::post('general/update-product-need', [GeneralController::class, 'updateProductNeed']);
    Route::post('general/order/update-notes', [GeneralController::class, 'updateOrderNotes']);
    Route::post('general/user', [GeneralController::class, 'loadUserById']);
    Route::get('general/sales-order/{type}', [GeneralController::class, 'getSalesOrder']);
    Route::get('general/sales-order/items/{uid_lead}', [GeneralController::class, 'getSalesOrderItems']);
    Route::get('general/purchase-order', [GeneralController::class, 'getPurchaseOrder']);
    Route::get('general/purchase-order/items/{uid_lead}', [GeneralController::class, 'getPurchaseOrderItems']);
    Route::get('general/checkbook', [GeneralController::class, 'getCheckbookData']);
    Route::post('general/swith-account', [GeneralController::class, 'switchAccount']);
    Route::get('general/notifications', [GeneralController::class, 'getNotifications']);
    Route::post('general/notification/read', [GeneralController::class, 'readNotifications']);
    Route::get('general/order-konsi/{parent_id}', [GeneralController::class, 'getNoKonsi']);
    Route::get('general/delete-product/{type}/{id}', [GeneralController::class, 'deleteProductNeed']);
    Route::post('general/logout', [GeneralController::class, 'logoutUser']);

    Route::get('generate-referal', [GeneralController::class, 'updateReferal']);
    Route::get('general/import-validation/{product_type?}', [GeneralController::class, 'loadImportValidationData']);
    Route::get('general/import-validation-users', [GeneralController::class, 'loadImportValidationDataUser']);
    Route::get('general/import-validation-roles', [GeneralController::class, 'loadImportValidationDataRole']);
    Route::get('general/import-validation-brands', [GeneralController::class, 'loadImportValidationDataBrand']);
    Route::get('general/import-validation-bins', [GeneralController::class, 'loadImportValidationDataBin']);
    Route::get('general/import-validation-konsinyasi', [GeneralController::class, 'loadImportValidationDataKonsinyasi']);
    Route::post('general/search-bin', [GeneralController::class, 'getSearchBin']);
    Route::post('general/switch-default-address', [GeneralController::class, 'switchDefaultAddress']);

    // master data
    // brand
    Route::post('master/brand', [BrandController::class, 'listBrand']);
    Route::get('master/brand/{brand_id}', [BrandController::class, 'getDetailBrand']);
    Route::post('master/brand/save', [BrandController::class, 'saveBrand']);
    Route::post('master/brand/save/{brand_id}', [BrandController::class, 'updateBrand']);
    Route::delete('master/brand/delete/{brand_id}', [BrandController::class, 'deleteBrand']);

    // company_account
    Route::post('master/company-account', [CompanyAccountController::class, 'listCompanyAccount']);
    Route::get('master/company-account/{company_account_id}', [CompanyAccountController::class, 'getDetailCompanyAccount']);
    Route::post('master/company-account/save', [CompanyAccountController::class, 'saveCompanyAccount']);
    Route::post('master/company-account/save/{company_account_id}', [CompanyAccountController::class, 'updateCompanyAccount']);
    Route::delete('master/company-account/delete/{company_account_id}', [CompanyAccountController::class, 'deleteCompanyAccount']);
    Route::post('master/company-account/status/{company_account_id}', [CompanyAccountController::class, 'updateStatusCompanyAccount']);

    // product carton
    Route::post('master/product-carton', [ProductCartonController::class, 'listProductCarton']);
    Route::get('master/product-carton/{product_carton_id}', [ProductCartonController::class, 'getDetailProductCarton']);
    Route::post('master/product-carton/save', [ProductCartonController::class, 'saveProductCarton']);
    Route::post('master/product-carton/save/{product_carton_id}', [ProductCartonController::class, 'updateProductCarton']);
    Route::delete('master/product-carton/delete/{product_carton_id}', [ProductCartonController::class, 'deleteProductCarton']);

    // banner
    Route::post('master/banner', [BannerController::class, 'listBanner']);
    Route::get('master/banner/{banner_id}', [BannerController::class, 'getDetailBanner']);
    Route::post('master/banner/save', [BannerController::class, 'saveBanner']);
    Route::post('master/banner/save/{banner_id}', [BannerController::class, 'updateBanner']);
    Route::delete('master/banner/delete/{banner_id}', [BannerController::class, 'deleteBanner']);

    // url shortener
    Route::get('master/url-shortener', [UrlShortenerController::class, 'listUrlShortener']);
    Route::get('master/url-shortener/{url_shortener_id}', [UrlShortenerController::class, 'getDetailUrlShortener']);
    Route::post('master/url-shortener', [UrlShortenerController::class, 'saveUrlShortener']); // Frontend expects this route
    Route::put('master/url-shortener/{url_shortener_id}', [UrlShortenerController::class, 'updateUrlShortener']); // Frontend expects this route
    Route::post('master/url-shortener/save', [UrlShortenerController::class, 'saveUrlShortener']);
    Route::post('master/url-shortener/save/{url_shortener_id}', [UrlShortenerController::class, 'updateUrlShortener']);
    Route::delete('master/url-shortener/{url_shortener_id}', [UrlShortenerController::class, 'deleteUrlShortener']); // Also fix delete route
    Route::delete('master/url-shortener/delete/{url_shortener_id}', [UrlShortenerController::class, 'deleteUrlShortener']);
    Route::post('master/url-shortener/generate-short-code', [UrlShortenerController::class, 'generateShortCode']);
    Route::post('master/url-shortener/extract-parameters', [UrlShortenerController::class, 'extractUrlParameters']);

    // category
    Route::post('master/category', [CategoryController::class, 'listCategory']);
    Route::get('master/category/{category_id}', [CategoryController::class, 'getDetailCategory']);
    Route::post('master/category/save', [CategoryController::class, 'saveCategory']);
    Route::post('master/category/save/{category_id}', [CategoryController::class, 'updateCategory']);
    Route::delete('master/category/delete/{category_id}', [CategoryController::class, 'deleteCategory']);

    // point
    Route::post('master/point', [MasterPointController::class, 'listMasterPoint']);
    Route::get('master/point/{master_point_id}', [MasterPointController::class, 'getDetailMasterPoint']);
    Route::post('master/point/save', [MasterPointController::class, 'saveMasterPoint']);
    Route::post('master/point/save/{master_point_id}', [MasterPointController::class, 'updateMasterPoint']);
    Route::delete('master/point/delete/{master_point_id}', [MasterPointController::class, 'deleteMasterPoint']);

    // package
    Route::post('master/package', [PackageController::class, 'listPackage']);
    Route::post('master/package/sync', [PackageController::class, 'syncGpData']);
    Route::get('master/package/{package_id}', [PackageController::class, 'getDetailPackage']);
    Route::post('master/package/save', [PackageController::class, 'savePackage']);
    Route::post('master/package/save/{package_id}', [PackageController::class, 'updatePackage']);
    Route::delete('master/package/delete/{package_id}', [PackageController::class, 'deletePackage']);


    // payment-method
    Route::post('master/payment-method', [PaymentMethodController::class, 'listPaymentMethod']);
    Route::get('master/payment-method-parents', [PaymentMethodController::class, 'getParentsData']);
    Route::get('master/payment-method/{payment_method_id}', [PaymentMethodController::class, 'getDetailPaymentMethod']);
    Route::post('master/payment-method/save', [PaymentMethodController::class, 'savePaymentMethod']);
    Route::post('master/payment-method/save/{payment_method_id}', [PaymentMethodController::class, 'updatePaymentMethod']);
    Route::delete('master/payment-method/delete/{payment_method_id}', [PaymentMethodController::class, 'deletePaymentMethod']);


    // variant
    Route::post('master/variant', [VariantController::class, 'listVariant']);
    Route::get('master/variant/{variant_id}', [VariantController::class, 'getDetailVariant']);
    Route::post('master/variant/save', [VariantController::class, 'saveVariant']);
    Route::post('master/variant/save/{variant_id}', [VariantController::class, 'updateVariant']);
    Route::delete('master/variant/delete/{variant_id}', [VariantController::class, 'deleteVariant']);

    // voucher
    Route::post('master/voucher', [VoucherController::class, 'listVoucher']);
    Route::get('master/voucher/{payment_method_id}', [VoucherController::class, 'getDetailVoucher']);
    Route::post('master/voucher/save', [VoucherController::class, 'saveVoucher']);
    Route::post('master/voucher/save/{payment_method_id}', [VoucherController::class, 'updateVoucher']);
    Route::delete('master/voucher/delete/{payment_method_id}', [VoucherController::class, 'deleteVoucher']);

    // payment term
    Route::post('master/payment-term', [PaymentTermController::class, 'listPaymentTerm']);
    Route::post('master/payment-term/sync', [PaymentTermController::class, 'syncGpData']);
    Route::get('master/payment-term/{payment_term_id}', [PaymentTermController::class, 'getDetailPaymentTerm']);
    Route::post('master/payment-term/save', [PaymentTermController::class, 'savePaymentTerm']);
    Route::post('master/payment-term/save/{payment_term_id}', [PaymentTermController::class, 'updatePaymentTerm']);
    Route::delete('master/payment-term/delete/{payment_term_id}', [PaymentTermController::class, 'deletePaymentTerm']);

    // master tax
    Route::post('master/master-tax', [MasterTaxController::class, 'listMasterTax']);
    Route::post('master/master-tax/sync', [MasterTaxController::class, 'syncGpData']);
    Route::get('master/master-tax/{master_tax_id}', [MasterTaxController::class, 'getDetailMasterTax']);
    Route::post('master/master-tax/save', [MasterTaxController::class, 'saveMasterTax']);
    Route::post('master/master-tax/save/{master_tax_id}', [MasterTaxController::class, 'updateMasterTax']);
    Route::delete('master/master-tax/delete/{master_tax_id}', [MasterTaxController::class, 'deleteMasterTax']);

    // master siteId
    Route::post('master/site-id', [MasterSiteIDController::class, 'listMasterSiteId']);
    Route::get('master/site-id/{master_site_id}', [MasterSiteIDController::class, 'getDetailMasterSiteId']);
    Route::post('master/site-id/save', [MasterSiteIDController::class, 'saveMasterSiteId']);
    Route::post('master/site-id/save/{master_site_id}', [MasterSiteIDController::class, 'updateMasterSiteId']);
    Route::delete('master/site-id/delete/{master_site_id}', [MasterSiteIDController::class, 'deleteMasterSiteId']);

    // master batchId
    Route::post('master/batchId', [MasterBatchIDController::class, 'listMasterBatchId']);
    Route::get('master/batchId/{master_batch_id}', [MasterBatchIDController::class, 'getDetailMasterBatchId']);
    Route::post('master/batchId/save', [MasterBatchIDController::class, 'saveMasterBatchId']);
    Route::post('master/batchId/save/{master_batch_id}', [MasterBatchIDController::class, 'updateMasterBatchId']);
    Route::delete('master/batchId/delete/{master_batch_id}', [MasterBatchIDController::class, 'deleteMasterBatchId']);

    // master pph
    Route::post('master/master-pph', [MasterPphController::class, 'listMasterPph']);
    Route::get('master/master-pph/{master_pph_id}', [MasterPphController::class, 'getDetailMasterPph']);
    Route::post('master/master-pph/save', [MasterPphController::class, 'saveMasterPph']);
    Route::post('master/master-pph/save/{master_pph_id}', [MasterPphController::class, 'updateMasterPph']);
    Route::delete('master/master-pph/delete/{master_pph_id}', [MasterPphController::class, 'deleteMasterPph']);

    // sku 
    Route::post('master/sku', [SkuController::class, 'listSku']);
    Route::get('master/sku/{sku_id}', [SkuController::class, 'getDetailSku']);
    Route::post('master/sku/save', [SkuController::class, 'saveSku']);
    Route::post('master/sku/save/{sku_id}', [SkuController::class, 'updateSku']);
    Route::delete('master/sku/delete/{sku_id}', [SkuController::class, 'deleteSku']);

    // ticket
    Route::post('ticket', [TicketController::class, 'listTicket']);
    Route::get('ticket/detail/{id}', [TicketController::class, 'detailTicket']);

    // warehouse 
    Route::post('master/warehouse', [WarehouseController::class, 'listWarehouse']);
    Route::get('master/warehouse/{warehouse_id}', [WarehouseController::class, 'getDetailWarehouse']);
    Route::post('master/warehouse/save', [WarehouseController::class, 'saveWarehouse']);
    Route::post('master/warehouse/save/{warehouse_id}', [WarehouseController::class, 'updateWarehouse']);
    Route::delete('master/warehouse/delete/{warehouse_id}', [WarehouseController::class, 'deleteWarehouse']);

    // master-bin 
    Route::post('master/master-bin', [MasterBinController::class, 'listMasterBin']);
    Route::get('master/master-bin/{master_bin_id}', [MasterBinController::class, 'getDetailMasterBin']);
    Route::post('master/master-bin/save', [MasterBinController::class, 'saveMasterBin']);
    Route::post('master/master-bin/save/{master_bin_id}', [MasterBinController::class, 'updateMasterBin']);
    Route::delete('master/master-bin/delete/{master_bin_id}', [MasterBinController::class, 'deleteMasterBin']);

    // master discount 
    Route::post('master/master-discount', [MasterDiscountController::class, 'listMasterDiscount']);
    Route::get('master/master-discount/{master_discount_id}', [MasterDiscountController::class, 'getDetailMasterDiscount']);
    Route::post('master/master-discount/save', [MasterDiscountController::class, 'saveMasterDiscount']);
    Route::post('master/master-discount/save/{master_discount_id}', [MasterDiscountController::class, 'updateMasterDiscount']);
    Route::delete('master/master-discount/delete/{master_discount_id}', [MasterDiscountController::class, 'deleteMasterDiscount']);

    // type case 
    Route::post('master/type-case', [TypeCaseController::class, 'listTypeCase']);
    Route::get('master/type-case/{type_case_id}', [TypeCaseController::class, 'getDetailTypeCase']);
    Route::post('master/type-case/save', [TypeCaseController::class, 'saveTypeCase']);
    Route::post('master/type-case/save/{type_case_id}', [TypeCaseController::class, 'updateTypeCase']);
    Route::delete('master/type-case/delete/{type_case_id}', [TypeCaseController::class, 'deleteTypeCase']);

    // category type case 
    Route::post('master/category-type-case', [CategoryCaseController::class, 'listCategoryCase']);
    Route::get('master/category-type-case/{category_type_case_id}', [CategoryCaseController::class, 'getDetailCategoryCase']);
    Route::post('master/category-type-case/save', [CategoryCaseController::class, 'saveCategoryCase']);
    Route::post('master/category-type-case/save/{category_type_case_id}', [CategoryCaseController::class, 'updateCategoryCase']);
    Route::delete('master/category-type-case/delete/{category_type_case_id}', [CategoryCaseController::class, 'deleteCategoryCase']);

    // status case 
    Route::post('master/status-case', [StatusCaseController::class, 'listStatusCase']);
    Route::get('master/status-case/{status_case_id}', [StatusCaseController::class, 'getDetailStatusCase']);
    Route::post('master/status-case/save', [StatusCaseController::class, 'saveStatusCase']);
    Route::post('master/status-case/save/{status_case_id}', [StatusCaseController::class, 'updateStatusCase']);
    Route::delete('master/status-case/delete/{status_case_id}', [StatusCaseController::class, 'deleteStatusCase']);

    // priority case 
    Route::post('master/priority-case', [PriorityCaseController::class, 'listPriorityCase']);
    Route::get('master/priority-case/{priority_case_id}', [PriorityCaseController::class, 'getDetailPriorityCase']);
    Route::post('master/priority-case/save', [PriorityCaseController::class, 'savePriorityCase']);
    Route::post('master/priority-case/save/{priority_case_id}', [PriorityCaseController::class, 'updatePriorityCase']);
    Route::delete('master/priority-case/delete/{priority_case_id}', [PriorityCaseController::class, 'deletePriorityCase']);

    // source case 
    Route::post('master/source-case', [SourceCaseController::class, 'listSourceCase']);
    Route::get('master/source-case/{source_case_id}', [SourceCaseController::class, 'getDetailSourceCase']);
    Route::post('master/source-case/save', [SourceCaseController::class, 'saveSourceCase']);
    Route::post('master/source-case/save/{source_case_id}', [SourceCaseController::class, 'updateSourceCase']);
    Route::delete('master/source-case/delete/{source_case_id}', [SourceCaseController::class, 'deleteSourceCase']);

    // level
    Route::post('master/level-price', [LevelController::class, 'listLevel']);
    Route::get('master/level-price/{level_id}', [LevelController::class, 'getDetailLevel']);
    Route::post('master/level-price/save', [LevelController::class, 'saveLevel']);
    Route::post('master/level-price/save/{level_id}', [LevelController::class, 'updateLevel']);
    Route::delete('master/level-price/delete/{level_id}', [LevelController::class, 'deleteLevel']);


    // pengemasan
    Route::post('master/pengemasan', [ProductAdditionalController::class, 'listProductAdditional']);
    Route::get('master/pengemasan/{product_additional_id}', [ProductAdditionalController::class, 'getDetailProductAdditional']);
    Route::post('master/pengemasan/save', [ProductAdditionalController::class, 'saveProductAdditional']);
    Route::post('master/pengemasan/save/{product_additional_id}', [ProductAdditionalController::class, 'updateProductAdditional']);
    Route::delete('master/pengemasan/delete/{product_additional_id}', [ProductAdditionalController::class, 'deleteProductAdditional']);

    // perlengkapan
    Route::post('master/perlengkapan', [ProductAdditionalController::class, 'listProductAdditional']);
    Route::get('master/perlengkapan/{product_additional_id}', [ProductAdditionalController::class, 'getDetailProductAdditional']);
    Route::post('master/perlengkapan/save', [ProductAdditionalController::class, 'saveProductAdditional']);
    Route::post('master/perlengkapan/save/{product_additional_id}', [ProductAdditionalController::class, 'updateProductAdditional']);
    Route::delete('master/perlengkapan/delete/{product_additional_id}', [ProductAdditionalController::class, 'deleteProductAdditional']);

    // vendor
    Route::post('master/vendor', [VendorController::class, 'listVendor']);
    Route::post('master/vendor/sync', [VendorController::class, 'syncGpData']);
    Route::get('master/vendor/{id}', [VendorController::class, 'getDetailVendor']);
    Route::post('master/vendor/save', [VendorController::class, 'saveVendor']);
    Route::post('master/vendor/save/{id}', [VendorController::class, 'updateVendor']);
    Route::delete('master/vendor/delete/{id}', [VendorController::class, 'deleteVendor']);
    Route::post('master/vendor/submit-gp', [VendorController::class, 'submitGP']);

    // vendor
    Route::post('master/checkbook', [CheckbookController::class, 'listCheckbook']);
    Route::post('master/checkbook/sync', [CheckbookController::class, 'syncGpData']);
    Route::get('master/checkbook/{checkbook_id}', [CheckbookController::class, 'getDetailCheckbook']);
    Route::post('master/checkbook/save', [CheckbookController::class, 'saveCheckbook']);
    Route::post('master/checkbook/save/{checkbook_id}', [CheckbookController::class, 'updateCheckbook']);
    Route::delete('master/checkbook/delete/{checkbook_id}', [CheckbookController::class, 'deleteCheckbook']);

    // sales-channel
    Route::post('master/sales-channel', [SalesChannelController::class, 'listSalesChannel']);
    Route::get('master/sales-channel/{sales_channel_id}', [SalesChannelController::class, 'getDetailSalesChannel']);
    Route::post('master/sales-channel/save', [SalesChannelController::class, 'saveSalesChannel']);
    Route::post('master/sales-channel/save/{sales_channel_id}', [SalesChannelController::class, 'updateSalesChannel']);
    Route::delete('master/sales-channel/delete/{sales_channel_id}', [SalesChannelController::class, 'deleteSalesChannel']);

    // shipping method
    Route::post('master/online-logistic', [LogisticController::class, 'listLogistic']);
    Route::post('master/online-logistic/rates', [LogisticController::class, 'listLogisticRates']);
    Route::post('master/online-logistic/update', [LogisticController::class, 'updateStatusLogistic']);
    Route::post('master/online-logistic/sync/logistic', [LogisticController::class, 'updateSyncLogistic']);
    Route::post('master/online-logistic/rates/update', [LogisticController::class, 'updateStatusLogisticRates']);
    Route::get('master/online-logistic/rates/discount/{logistic_rate_id}', [LogisticController::class, 'getLogisticDiscount']);
    Route::post('master/online-logistic/rates/discount/save', [LogisticController::class, 'saveLogisticDiscount']);

    Route::post('master/offline-logistic/save/{logistic_id?}', [LogisticController::class, 'saveLogistic']);
    Route::post('master/offline-logistic/rates/save/{logistic_rates_id?}', [LogisticController::class, 'saveLogisticRates']);
    Route::delete('master/offline-logistic/delete/{logistic_id}', [LogisticController::class, 'deleteLogistic']);
    Route::delete('master/offline-logistic/rates/delete/{logistic_rates_id}', [LogisticController::class, 'deleteLogisticRates']);

    Route::post('master/shipping-method/offline/logistic/save/{logistic_id?}', [LogisticController::class, 'saveLogistic']);
    Route::post('master/shipping-method/offline/logistic/rates/save/{logistic_rates_id?}', [LogisticController::class, 'saveLogisticRates']);
    Route::delete('master/shipping-method/offline/logistic/delete/{logistic_id}', [LogisticController::class, 'deleteLogistic']);
    Route::delete('master/shipping-method/offline/logistic/rates/delete/{logistic_rates_id}', [LogisticController::class, 'deleteLogisticRates']);

    Route::post('master/ongkir', [MasterOngkirController::class, 'listMasterOngkir']);
    Route::get('master/ongkir/{master_ongkir_id}', [MasterOngkirController::class, 'getDetailMasterOngkir']);
    Route::post('master/ongkir/save', [MasterOngkirController::class, 'saveMasterOngkir']);
    Route::post('master/ongkir/save/{master_ongkir_id}', [MasterOngkirController::class, 'updateMasterOngkir']);
    Route::delete('master/ongkir/delete/{master_ongkir_id}', [MasterOngkirController::class, 'deleteMasterOngkir']);

    // notif
    Route::post('master/notification', [NotifController::class, 'listNotif']);
    Route::get('master/notification/{notif_id}', [NotifController::class, 'getDetailNotif']);
    Route::post('master/notification/save', [NotifController::class, 'saveNotif']);
    Route::post('master/notification/save/{notif_id}', [NotifController::class, 'updateNotif']);
    Route::delete('master/notification/delete/{notif_id}', [NotifController::class, 'deleteNotif']);

    // rate-limit
    Route::post('master/rate-limit', [RateLimitSettingController::class, 'listRole']);
    Route::post('master/rate-limit/update/{role_id}', [RateLimitSettingController::class, 'updateStatus']);

    // site-management/role
    Route::post('site-management/role', [RoleController::class, 'listRole']);
    Route::get('site-management/role/{role_id}', [RoleController::class, 'getDetailRole']);
    Route::post('site-management/role/save', [RoleController::class, 'saveRole']);
    Route::post('site-management/role/save/{role_id}', [RoleController::class, 'updateRole']);
    Route::delete('site-management/role/delete/{role_id}', [RoleController::class, 'deleteRole']);

    // master data general
    Route::get('master/brand', [MasterController::class, 'getBrand']);
    Route::get('master/product-carton/{id}', [MasterController::class, 'getProductCartonById']);
    Route::get('master/product/{id}', [MasterController::class, 'getProductMasterById']);
    Route::get('master/categories', [MasterController::class, 'getCategory']);
    Route::get('master/bussiness-entity', [MasterController::class, 'getBussinnesEntity']);
    Route::get('master/role/{role_user?}', [MasterController::class, 'getRole']);
    Route::get('master/role-requisition', [MasterController::class, 'getRoleRequisition']);
    Route::get('master/warehouse', [MasterController::class, 'getWarehouse']);
    Route::get('master/bin', [MasterController::class, 'getMasterBin']);
    Route::get('master/top', [MasterController::class, 'getTop']);
    Route::get('master/sku', [MasterController::class, 'getSku']);
    Route::get('master/product-carton', [MasterController::class, 'getProductCarton']);
    Route::get('master/variant', [MasterController::class, 'getVariant']);
    Route::get('master/products/{sales_channel?}', [MasterController::class, 'getProductList']);
    Route::get('master/product-lists', [MasterController::class, 'getProductListMaster']);
    Route::get('master/product-variant-lists', [MasterController::class, 'getProductListVariant']);
    Route::post('master/product/stocks', [MasterController::class, 'getProductStockMaster']);
    Route::get('master/products/additional/{type}', [MasterController::class, 'getProductAdditionalList']);
    Route::get('master/taxs', [MasterController::class, 'getMasterTax']);
    Route::get('master/vendors', [MasterController::class, 'getVendors']);
    Route::get('master/discounts/{sales_channel?}', [MasterController::class, 'getMasterDiscount']);
    Route::get('master/package', [MasterController::class, 'getPackage']);
    Route::get('master/company-account', [MasterController::class, 'getCompanyAccount']);
    Route::get('master/logistic/offline', [MasterController::class, 'getOfflineExpedition']);
    Route::get('master/logistic', [MasterController::class, 'getLogistic']);
    Route::get('master/type-case', [MasterController::class, 'getTypeCase']);
    Route::get('master/source-case', [MasterController::class, 'getSourceCase']);
    Route::get('master/priority-case', [MasterController::class, 'getPriorityCase']);
    Route::get('master/status-case', [MasterController::class, 'getStatusCase']);
    Route::get('master/category-case', [MasterController::class, 'getCategoryCase']);
    Route::get('master/list-case', [MasterController::class, 'getCaseList']);
    Route::post('master/list-case', [MasterController::class, 'getProductByCase']);
    Route::get('master/provinsi', [MasterController::class, 'getProvinsi']);
    Route::get('master/kabupaten/{id}', [MasterController::class, 'getKota']);
    Route::get('master/kecamatan/{id}', [MasterController::class, 'getKecamatan']);
    Route::get('master/kelurahan/{id}', [MasterController::class, 'getKelurahan']);
    Route::post('master/address/search', [MasterController::class, 'searchAddress']);
    Route::post('master/search/user', [MasterController::class, 'loadUserByPhone']);
    Route::get('master/site', [MasterController::class, 'getWhid']);
    Route::get('master/batchId', [MasterController::class, 'getBatchId']);
    Route::get('master/so-konsinyasi', [MasterController::class, 'getSoKonsinyasi']);
    Route::get('master/so-konsinyasi/{id}', [MasterController::class, 'getDetailKonsinyasi']);
    Route::get('master/bin-by-contact/{id}', [MasterController::class, 'getBinByContact']);
    Route::get('master/contact-by-bin/{id}', [MasterController::class, 'getContactBin']);
    Route::get('master/purchase-request', [MasterController::class, 'getApprovedPurchaseRequests']);
    Route::get('master/purchase-request/{id}', [MasterController::class, 'getDetailPr']);
    Route::get('master/payment-term', [MasterController::class, 'getPaymentTerm']);
    Route::get('master/payment-method-list', [MasterController::class, 'getPaymentMethod']);

    // checkout agent
    Route::get('cart', [CheckoutAgent::class, 'getCart']);
    Route::get('cart/delete/{cart_id}', [CheckoutAgent::class, 'deleteCart']);
    Route::get('cart/add-qty/{cart_id}', [CheckoutAgent::class, 'addQty']);
    Route::post('cart/update-qty/{cart_id}', [CheckoutAgent::class, 'updateChartQty']);
    Route::get('cart/remove-qty/{cart_id}', [CheckoutAgent::class, 'minusQty']);
    Route::get('cart/selectAll', [CheckoutAgent::class, 'selectAll']);
    Route::get('cart/select/{cart_id}', [CheckoutAgent::class, 'selectItem']);
    Route::post('cart/select-variant', [CheckoutAgent::class, 'selectVariant']);
    Route::get('cart/payment-method', [CheckoutAgent::class, 'getPaymentMethod']);
    Route::get('cart/warehouse', [CheckoutAgent::class, 'getWarehouse']);
    Route::get('cart/address', [CheckoutAgent::class, 'getAddress']);

    // contact
    Route::post('contact', [ContactController::class, 'listContact']);
    Route::post('contact/save-contact', [ContactController::class, 'storeContact']);
    Route::post('contact/service/search-user', [ContactController::class, 'getUserCreatedBy']);
    Route::post('contact/service/sync-gp/{user_id}', [ContactController::class, 'syncGp']);
    Route::get('contact/service/sync', [ContactController::class, 'syncGpData']);
    Route::get('contact/detail/{user_id}', [ContactController::class, 'detailContact']);
    Route::post('contact/voucher', [ContactController::class, 'getVoucher']);
    Route::get('contact/detail/transaction/active/{user_id}', [ContactController::class, 'contactTransaction']);
    Route::get('contact/detail/transaction/history/{user_id}', [ContactController::class, 'contactTransactionHistory']);
    Route::get('contact/detail/case/history/{user_id}', [ContactController::class, 'contactHistoryCase']);
    Route::post('contact/detail/update', [ContactController::class, 'updateProfileContact']);
    Route::post('contact/rate-limit/update/{user_id?}', [ContactController::class, 'updateRateLimit']);
    Route::get('contact/black-list/{user_id}', [ContactController::class, 'blackListUser']);
    Route::post('contact/address/save-address', [ContactController::class, 'saveAddress']);
    Route::post('contact/address/set-default-address', [ContactController::class, 'setDefaultAddress']);
    Route::get('contact/address/delete/{address_id}', [ContactController::class, 'deleteContact']);
    Route::post('contact/downline/member/list/{user_id}', [ContactController::class, 'getMemberDownline']);
    Route::post('contact/downline/member/save/{user_id}', [ContactController::class, 'saveMember']);
    Route::delete('contact/downline/member/delete/{downline_id}', [ContactController::class, 'deleteMember']);
    Route::post('contact/export', [ContactController::class, 'export']);
    Route::post('contact/import', [ContactController::class, 'import']);
    Route::post('contact/submit-gp', [ContactController::class, 'submitGP']);

    // contact group
    Route::post('contact-group', [ContactGroupController::class, 'listContactGroup']);
    Route::post('contact-group/member', [ContactGroupController::class, 'listContactMember']);
    Route::get('contact-group/detail/{group_id}', [ContactGroupController::class, 'detailContactGroup']);
    Route::post('contact-group/save/{group_id?}', [ContactGroupController::class, 'saveContactGroup']);
    Route::delete('contact-group/delete/{group_id?}', [ContactGroupController::class, 'deleteContactGroup']);

    Route::post('so/import', [OrderManualController::class, 'import']);
    //Trans Agent
    Route::post('transAgent', [TransAgentController::class, 'listTransAgentAll']);
    Route::post('transAgentWaitingPayment', [TransAgentController::class, 'listTransAgentWaitingPayment']);
    Route::post('confirmationAgent', [TransAgentController::class, 'confirmation']);
    Route::post('newTransactionAgent', [TransAgentController::class, 'newTransaction']);
    Route::post('warehouseAgent', [TransAgentController::class, 'warehouse']);
    Route::post('readyProductAgent', [TransAgentController::class, 'readyProduct']);
    Route::post('deliveryAgent', [TransAgentController::class, 'delivery']);
    Route::post('orderAcceptedAgent', [TransAgentController::class, 'orderAccepted']);
    Route::post('historyAgent', [TransAgentController::class, 'history']);
    Route::get('trans-agent/detail/{id}', [TransAgentController::class, 'detailTransAgent']);
    Route::post('trans-agent/assign-warehouse/{id}', [TransAgentController::class, 'assignWarehouse']);
    Route::post('trans-agent/packing-process/{id}', [TransAgentController::class, 'packingProcess']);
    Route::post('trans-agent/product-receive/{id}', [TransAgentController::class, 'productReceived']);

    // bulk
    Route::post('trans-agent/bulk/invoice', [TransAgentController::class, 'bulkInvoice']);


    Route::get('genie/order/sync', [GenieController::class, 'syncData']);
    Route::get('genie/order/cancel-sync', [GenieController::class, 'cancelSync']);
    Route::get('genie/order/detail/{orderId}', [GenieController::class, 'detail']);
    Route::get('genie/order/sync-check', [GenieController::class, 'checkSync']);
    Route::post('genie/order/list', [GenieController::class, 'orderList']);
    Route::post('genie/sync/gp', [GenieController::class, 'submitGp']);
    Route::post('genie/dashboard', [GenieController::class, 'dashboardDetail']);
    Route::post('genie-order/export', [GenieController::class, 'export']);

    // Ethix
    Route::post('ethix/dashboard', [MpethixController::class, 'dashboardDetail']);
    Route::post('ethix/order/list', [MpethixController::class, 'orderList']);
    Route::get('ethix/detail/{orderId}', [MpethixController::class, 'detail']);

    // Gp
    Route::post('channel/gp/list', [GPSubmissionController::class, 'submissionList']);
    Route::post('channel/gp/list/detail/{list_id}', [GPSubmissionController::class, 'submissionListDetail']);


    // Bin
    Route::post('bin/list', [BinController::class, 'listBin']);
    Route::post('detail/bin/{product_variant_id}', [BinController::class, 'listBinDetail']);
    Route::post('bin/import', [BinController::class, 'import']);
    Route::post('bin/export', [BinController::class, 'export']);

    // Generate Barcode
    Route::post('barcode/list', [BinController::class, 'listBin']);
    Route::post('detail/barcode/{barcode_id}', [BinController::class, 'listBinDetail']);

    // order lead
    Route::post('order-lead', [OrderLeadController::class, 'listOrderLead']);
    Route::get('order-lead', [OrderLeadController::class, 'listOrderLead']);
    Route::get('order-lead/{uid_lead}', [OrderLeadController::class, 'detailOrderLead']);
    Route::get('order-lead/sales/channel', [OrderLeadController::class, 'getSalesChannel']);
    Route::post('order-lead/change-courier', [OrderLeadController::class, 'changeCourier']);
    Route::post('order-lead/service/search-contact', [OrderLeadController::class, 'getUserContact']);
    Route::post('order-lead/service/search-sales', [OrderLeadController::class, 'getUserSales']);
    Route::get('order-lead/assign-warehouse/{uid_lead}', [OrderLeadController::class, 'assignWarehouse']);
    Route::post('order-lead/billing', [OrderLeadController::class, 'billing']);
    Route::post('order-lead/billing/verify', [OrderLeadController::class, 'billingVerify']);
    Route::get('order-lead/cancel/{uid_lead}', [OrderLeadController::class, 'cancel']);
    Route::get('order-lead/closed/{uid_lead}', [OrderLeadController::class, 'setClosed']);
    Route::post('order-lead/reminder/save', [OrderLeadController::class, 'saveReminder']);
    Route::post('order-lead/reminder/update', [OrderLeadController::class, 'updateReminder']);
    Route::get('order-lead/reminder/delete/{reminder_id}', [OrderLeadController::class, 'deleteReminder']);
    Route::post('order-lead/shipping/save', [OrderLeadController::class, 'saveOrderShipping']);
    Route::post('order-lead/update/kode-unik', [OrderLeadController::class, 'deleteUniqueCode']);
    Route::post('order-lead/update/ongkir/{uid_lead}', [OrderLeadController::class, 'updateOngkosKirim']);
    Route::post('order-lead/export', [OrderLeadController::class, 'export']);
    Route::post('order-lead/export/detail/{uid}', [OrderLeadController::class, 'exportDetail']);
    Route::post('order-lead/split-delivery-order', [OrderLeadController::class, 'splitOrderDelivery']);
    Route::get('order-lead/delivery/cancel/{delivery_id}', [OrderLeadController::class, 'cancelOrderDelivery']);

    // order lead
    Route::post('order-manual', [OrderManualController::class, 'listOrderLead']);
    Route::get('order-manual', [OrderManualController::class, 'listOrderLead']);
    Route::get('order-manual/{uid_lead}', [OrderManualController::class, 'detailOrderLead']);
    Route::get('order-manual/detail/{uid_lead}', [OrderManualController::class, 'detailOrder']);
    Route::get('order-manual/sales/channel/{account_id?}', [OrderManualController::class, 'getSalesChannel']);
    Route::post('order-manual/change-courier', [OrderManualController::class, 'changeCourier']);
    Route::post('order-manual/service/search-contact', [OrderManualController::class, 'getUserContact']);
    Route::post('order-manual/service/search-sales', [OrderManualController::class, 'getUserSales']);
    Route::get('order-manual/assign-warehouse/{uid_lead}', [OrderManualController::class, 'assignWarehouse']);
    Route::get('order-manual/billing/list/{uid_lead}', [OrderManualController::class, 'getListBilling']);
    Route::post('order-manual/billing', [OrderManualController::class, 'billing']);
    Route::post('order-manual/billing/verify', [OrderManualController::class, 'billingVerify']);
    Route::post('order-manual/delivery', [OrderManualController::class, 'setDelivery']);
    Route::get('order-manual/cancel/{uid_lead}', [OrderManualController::class, 'cancel']);
    Route::get('order-manual/closed/{uid_lead}', [OrderManualController::class, 'setClosed']);
    Route::post('order-manual/reminder/save', [OrderManualController::class, 'saveReminder']);
    Route::post('order-manual/reminder/update', [OrderManualController::class, 'updateReminder']);
    Route::get('order-manual/reminder/delete/{reminder_id}', [OrderManualController::class, 'deleteReminder']);
    Route::get('order-manual/uid/get', [OrderManualController::class, 'getUidLead']);
    Route::post('order-manual/form/save/{uid_lead?}', [OrderManualController::class, 'saveOrderManual']);
    Route::post('order-manual/form/update', [OrderManualController::class, 'updateOrderManual']);
    Route::post('order-manual/product-items', [OrderManualController::class, 'selectProductItems']);
    Route::post('order-manual/product-items/add', [OrderManualController::class, 'addProductItem']);
    Route::post('order-manual/product-items/delete', [OrderManualController::class, 'deleteProductItem']);
    Route::post('order-manual/product-items/add-qty', [OrderManualController::class, 'addQty']);
    Route::post('order-manual/product-items/remove-qty', [OrderManualController::class, 'removeQty']);
    Route::get('order-manual/product-need/{uid_lead}', [OrderManualController::class, 'getProductNeed']);
    Route::post('order-manual/product-need/invoice/{product_need_id?}', [OrderManualController::class, 'updateInvoiced']);
    Route::post('order-manual/shipping/save', [OrderManualController::class, 'saveOrderShipping']);
    Route::post('order-manual/update/kode-unik', [OrderManualController::class, 'deleteUniqueCode']);
    Route::post('order-manual/update/ongkir/{uid_lead}', [OrderManualController::class, 'updateOngkosKirim']);
    Route::post('order-manual/export', [OrderManualController::class, 'export']);
    Route::post('order-manual/export/detail/{uid}', [OrderManualController::class, 'exportDetail']);
    Route::post('order-manual/split-delivery-order', [OrderManualController::class, 'splitOrderDelivery']);
    Route::get('order-manual/delivery/cancel/{delivery_id}', [OrderManualController::class, 'cancelOrderDelivery']);

    // freebies
    Route::post('freebies', [OrderFreeBiesController::class, 'listOrderLead']);
    Route::get('freebies', [OrderFreeBiesController::class, 'listOrderLead']);
    Route::get('freebies/{uid_lead}', [OrderFreeBiesController::class, 'detailOrderLead']);
    Route::post('freebies/change-courier', [OrderFreeBiesController::class, 'changeCourier']);
    Route::post('freebies/service/search-contact', [OrderFreeBiesController::class, 'getUserContact']);
    Route::post('freebies/service/search-sales', [OrderFreeBiesController::class, 'getUserSales']);
    Route::get('freebies/assign-warehouse/{uid_lead}', [OrderFreeBiesController::class, 'assignWarehouse']);
    Route::post('freebies/billing', [OrderFreeBiesController::class, 'billing']);
    Route::post('freebies/billing/verify', [OrderFreeBiesController::class, 'billingVerify']);
    Route::post('freebies/delivery', [OrderFreeBiesController::class, 'setDelivery']);
    Route::get('freebies/cancel/{uid_lead}', [OrderFreeBiesController::class, 'cancel']);
    Route::get('freebies/closed/{uid_lead}', [OrderFreeBiesController::class, 'setClosed']);
    Route::post('freebies/reminder/save', [OrderFreeBiesController::class, 'saveReminder']);
    Route::post('freebies/reminder/update', [OrderFreeBiesController::class, 'updateReminder']);
    Route::get('freebies/reminder/delete/{reminder_id}', [OrderFreeBiesController::class, 'deleteReminder']);
    Route::get('freebies/uid/get', [OrderFreeBiesController::class, 'getUidLead']);
    Route::post('freebies/form/save/{uid_lead?}', [OrderFreeBiesController::class, 'saveOrderManual']);
    Route::post('freebies/product-items', [OrderFreeBiesController::class, 'selectProductItems']);
    Route::post('freebies/product-items/add', [OrderFreeBiesController::class, 'addProductItem']);
    Route::post('freebies/product-items/delete', [OrderFreeBiesController::class, 'deleteProductItem']);
    Route::post('freebies/product-items/add-qty', [OrderFreeBiesController::class, 'addQty']);
    Route::post('freebies/product-items/remove-qty', [OrderFreeBiesController::class, 'removeQty']);
    Route::post('freebies/product-items/remove-price', [OrderFreeBiesController::class, 'updatePrice']);
    Route::get('freebies/product-need/{uid_lead}', [OrderFreeBiesController::class, 'getProductNeed']);
    Route::post('freebies/shipping/save', [OrderFreeBiesController::class, 'saveOrderShipping']);
    Route::post('freebies/update/kode-unik', [OrderFreeBiesController::class, 'deleteUniqueCode']);
    Route::post('freebies/update/ongkir/{uid_lead}', [OrderFreeBiesController::class, 'updateOngkosKirim']);
    Route::post('freebies/export', [OrderFreeBiesController::class, 'export']);
    Route::post('freebies/export/detail/{uid}', [OrderFreeBiesController::class, 'exportDetail']);
    Route::post('freebies/split-delivery-order', [OrderFreeBiesController::class, 'splitOrderDelivery']);
    Route::get('freebies/delivery/cancel/{delivery_id}', [OrderFreeBiesController::class, 'cancelOrderDelivery']);
    Route::post('freebies/import', [OrderFreeBiesController::class, 'import']);

    Route::post('order/invoice/list', [OrderInvoiceController::class, 'listOrderInvoice']);
    Route::get('order/invoice/delivery/{uid_lead}', [OrderInvoiceController::class, 'getOrderDelivery']);
    Route::post('order/invoice/konsinyasi/delivery', [OrderKonsinyasiController::class, 'loadOrderDelivery']);
    Route::get('order/invoice/detail/{uid_lead}/{uid_delivery}', [OrderInvoiceController::class, 'getOrderDetail']);
    Route::post('order/invoice/submit', [OrderInvoiceController::class, 'submitInvoice']);
    Route::post('order/invoice/cancel/{invoice_id}', [OrderInvoiceController::class, 'cancelInvoice']);
    Route::post('order/invoice/submit/bulk/klik-pajak', [OrderInvoiceController::class, 'submitBulkKlikpajak']);
    Route::post('order/invoice/reset-gp', [OrderInvoiceController::class, 'resetGp']);
    Route::post('order/invoice/update/date', [OrderInvoiceController::class, 'updateInvoiceDate']);
    // Route::post('order/invoice/save', [OrderFreeBiesController::class, 'listOrderInvoice']);

    // order konsinyasi
    Route::post('order-konsinyasi', [OrderKonsinyasiController::class, 'listOrderLead']);
    Route::get('order-konsinyasi', [OrderKonsinyasiController::class, 'listOrderLead']);
    Route::get('order-konsinyasi/{uid_lead}', [OrderKonsinyasiController::class, 'detailOrderLead']);
    Route::get('order-konsinyasi/assign-warehouse/{uid_lead}', [OrderKonsinyasiController::class, 'assignWarehouse']);
    Route::post('order-konsinyasi/form/save/{uid_lead?}', [OrderKonsinyasiController::class, 'saveOrderKonsinyasi']);
    Route::post('order-konsinyasi/form/update', [OrderKonsinyasiController::class, 'updateOrderKonsinyasi']);
    Route::post('order-konsinyasi/import', [OrderKonsinyasiController::class, 'import']);
    Route::post('order-konsinyasi/tf/import', [OrderKonsinyasiController::class, 'importTransfer']);
    Route::post('order-konsinyasi/split-delivery-order', [OrderKonsinyasiController::class, 'splitOrderDelivery']);

    // SALES ORDER NEW
    Route::post('sales-order/items', [SalesOrderController::class, 'loadOrderItems']);
    Route::get('sales-order/invoice', [SalesOrderController::class, 'loadSalesOrderInvoice']);
    Route::get('sales-order', [SalesOrderController::class, 'listSalesOrder']);
    Route::get('sales-order/detail/{uid_lead}', [SalesOrderController::class, 'detailSalesOrder']);
    Route::get('sales-order/items/{uid_lead}', [SalesOrderController::class, 'detailSalesOrderItems']);
    Route::get('sales-order/delivery/{uid_lead}', [SalesOrderController::class, 'detailSalesOrderDeliveryItems']);
    Route::get('sales-order/billing/{uid_lead}', [SalesOrderController::class, 'detailSalesOrderBillingsItems']);
    Route::get('sales-order/channel/{type}/{account_id?}', [SalesOrderController::class, 'getSalesChannel']);
    // agent transaction
    Route::post('sales-order/agent/new-order', [SalesOrderController::class, 'newOrderAgent']);
    Route::post('sales-order/update/status', [SalesOrderController::class, 'updateStatus']);
    Route::post('sales-order/import', [SalesOrderController::class, 'importOrder']);
    Route::post('sales-order/export', [SalesOrderController::class, 'exportOrder']);
    Route::post('sales-order/change-address', [SalesOrderController::class, 'changeAddress']);



    // order submit gp
    Route::post('order/{type}/submit', [GpController::class, 'submitGp']);
    Route::post('order/manual/invoice/submit', [GpController::class, 'submitInvoiceSoGp']);
    Route::post('po/order/submit', [GpController::class, 'submitPoGp']);
    Route::post('receiving/po/submit', [GpController::class, 'submitReceivingPoGp']);
    Route::post('inventory/transfer/submit', [GpController::class, 'submitTransferEntryGp']);
    Route::post('order/submit/history', [GpController::class, 'listSubmitGp']);
    Route::post('order/submit/history/{submit_id}', [GpController::class, 'listSubmitGpDetail']);
    Route::post('marketplace/submit', [GpController::class, 'submitMarketPlace']);
    Route::post('marketplace/submit/ethix', [MarketPlaceController::class, 'submitMarketPlaceEthix']);
    Route::post('marketplace/update/warehouse', [MarketPlaceController::class, 'updateWarehouse']);
    Route::post('marketplace/history', [GpController::class, 'listSubmitGp']);
    Route::post('marketplace/history/{submit_id}', [GpController::class, 'listSubmitGpDetail']);
    Route::post('submit-ethix/history', [GpController::class, 'listSubmitGp']);
    Route::post('submit-ethix/history/{submit_id}', [GpController::class, 'listSubmitGpDetail']);
    Route::post('import-contact/history', [GpController::class, 'listSubmitGp']);
    Route::post('import-contact/history/{submit_id}', [GpController::class, 'listSubmitGpDetail']);
    Route::post('transaction/gp/submit', [GpController::class, 'submitTelmark']);
    Route::post('transaction/gp/history', [GpController::class, 'listSubmitGp']);
    Route::post('transaction/gp/history/{submit_id}', [GpController::class, 'listSubmitGpDetail']);

    // dashboard
    Route::post('dashboard', [DashboardController::class, 'detailDashboard']);

    // agent management
    Route::get('province-list', [AgentManagementController::class, 'listProvince']);
    Route::get('city-list/{province_id}', [AgentManagementController::class, 'listCity']);
    Route::get('agent-list/{city_id}', [AgentManagementController::class, 'listAgent']);
    Route::post('agent-update', [AgentManagementController::class, 'updateAgent']);
    Route::post('agent/re-order', [AgentManagementController::class, 'reOrder']);

    // domain
    Route::get('agent/domain', [AgentDomainManagementController::class, 'listAgentDomain']);
    Route::post('agent/domain/list', [AgentDomainManagementController::class, 'listAgentByDomain']);
    Route::post('agent/domain/save', [AgentDomainManagementController::class, 'saveAgentDomain']);
    Route::post('agent/domain/delete', [AgentDomainManagementController::class, 'deleteAgentDomain']);
    Route::post('agent/domain/update', [AgentDomainManagementController::class, 'updateAgentDomain']);
    Route::post('agent/domain/toggle', [AgentDomainManagementController::class, 'toggleAgentDomain']);

    // menu
    Route::get('menu/list', [MenuController::class, 'loadMenu']);
    Route::post('menu/create', [MenuController::class, 'createMenu']);
    Route::post('menu/update/{menu_id}', [MenuController::class, 'updateMenu']);
    Route::post('menu/copy/{menu_id}', [MenuController::class, 'copyMenu']);
    Route::post('menu/role/update/{menu_id}', [MenuController::class, 'updateMenuRole']);
    Route::delete('menu/delete/{menu_id}', [MenuController::class, 'deleteMenu']);
    Route::post('menu/order', [MenuController::class, 'orderMenu']);

    // Retur
    Route::post('case/return/list', [ReturnController::class, 'getListReturn']);
    Route::get('case/return/detail/{uid_retur}', [ReturnController::class, 'getReturnDetail']);
    Route::post('case/return/reject', [ReturnController::class, 'reject']);
    Route::post('case/return/approve', [ReturnController::class, 'approve']);

    // Refund
    Route::post('case/refund/list', [RefundController::class, 'getListrefund']);
    Route::get('case/refund/detail/{uid_retur}', [RefundController::class, 'getRefundnDetail']);
    Route::post('case/refund/reject', [RefundController::class, 'reject']);
    Route::post('case/refund/approve', [RefundController::class, 'approve']);

    // case
    Route::post('case/manual/list', [ManualController::class, 'getListManual']);
    Route::get('case/manual/detail/{uid_case}', [ManualController::class, 'getManualDetail']);
    Route::post('case/manual/save', [ManualController::class, 'createCase']);
    Route::post('case/manual/save/{uid_case}', [ManualController::class, 'updateCase']);

    // sales return
    Route::post('order/sales-return', [SalesReturnController::class, 'getListSalesReturn']);
    Route::get('order/sales-return/detail/{uid_return}', [SalesReturnController::class, 'getListSalesReturnDetail']);
    Route::post('order/sales-return/save', [SalesReturnController::class, 'saveSalesReturn']);
    Route::post('order/sales-return/product-items', [SalesReturnController::class, 'selectProductItems']);
    Route::post('order/sales-return/product-items/add', [SalesReturnController::class, 'addProductItem']);
    Route::post('order/sales-return/product-items/delete', [SalesReturnController::class, 'deleteProductItem']);
    Route::post('order/sales-return/product-items/add-qty', [SalesReturnController::class, 'addQty']);
    Route::post('order/sales-return/product-items/remove-qty', [SalesReturnController::class, 'removeQty']);
    Route::post('order/sales-return/data-order', [SalesReturnController::class, 'loadDataByOrderNumber']);
    Route::post('order/sales-return/billing', [SalesReturnController::class, 'addBilling']);
    Route::post('order/sales-return/billing/verify', [SalesReturnController::class, 'billingVerify']);
    Route::post('order/sales-return/save-resi', [SalesReturnController::class, 'saveResi']);
    Route::post('order/sales-return/assign-warehouse', [SalesReturnController::class, 'assignToWarehouse']);
    Route::post('order/sales-return/cancel', [SalesReturnController::class, 'cancel']);
    Route::post('order/sales-return/payment-proccess', [SalesReturnController::class, 'paymentProccess']);
    Route::post('order/sales-return/completed', [SalesReturnController::class, 'completed']);
    Route::post('order/sales-return/due-date', [SalesReturnController::class, 'getDueDate']);
    Route::post('order/sales-return/update/kode-unik', [SalesReturnController::class, 'deleteUniqueCode']);
    Route::post('order/sales-return/update/ongkir/{uid_retur}', [SalesReturnController::class, 'updateOngkosKirim']);
    Route::post('sales-return/export', [SalesReturnController::class, 'export']);
    Route::post('sales-return/export/detail/{uid}', [SalesReturnController::class, 'exportDetail']);



    // inventory
    Route::get('inventory/info/created', [InventoryController::class, 'getInfoCreated']);
    Route::get('inventory/item', [InventoryController::class, 'getProductCount']);
    Route::post('inventory/product/stock', [InventoryController::class, 'inventoryStock']);
    Route::get('inventory/product/detail/{inventory_id}', [InventoryController::class, 'inventoryStockDetail']);
    Route::get('inventory/trf-number', [InventoryController::class, 'getTrfNumber']);
    Route::get('inventory/so-number', [InventoryController::class, 'getSONumber']);
    Route::get('inventory/si-number', [InventoryController::class, 'getSINumber']);
    Route::post('inventory/product/stock/save', [InventoryController::class, 'inventoryStockCreate']);
    Route::post('inventory/product/transfer/save', [InventoryController::class, 'inventoryTransferCreate']);
    Route::post('inventory/product/transfer/save/{inventory_id}', [InventoryController::class, 'inventoryTransferUpdate']);
    Route::post('inventory/product/transfer/complete/{inventory_id}', [InventoryController::class, 'inventoryTransferComplete']);
    Route::post('inventory/product/transfer/approve/{inventory_id}', [InventoryController::class, 'inventoryTransferApprove']);
    Route::post('inventory/product/transfer/cancel/{inventory_id}', [InventoryController::class, 'inventoryTransferCancel']);
    Route::post('inventory/product/konsinyasi/save', [InventoryController::class, 'inventoryTransferCreate']);
    Route::post('inventory/product/konsinyasi/save/{inventory_id}', [InventoryController::class, 'inventoryTransferUpdate']);
    Route::post('inventory/product/konsinyasi/complete/{inventory_id}', [InventoryController::class, 'inventoryTransferComplete']);
    Route::post('inventory/product/konsinyasi/approve/{inventory_id}', [InventoryController::class, 'inventoryTransferApprove']);
    Route::post('inventory/product/konsinyasi/cancel/{inventory_id}', [InventoryController::class, 'inventoryTransferCancel']);
    Route::post('inventory/product/stock/update/{inventory_id}', [InventoryController::class, 'inventoryStockUpdate']);
    Route::post('inventory/product/stock/cancel/{inventory_id}', [InventoryController::class, 'inventoryStockCancel']);
    Route::post('inventory/product/stock/allocated/{inventory_id}', [InventoryController::class, 'inventoryStockAllocated']);
    Route::post('inventory/product/stock/retrigger-stock/{inventory_id}', [InventoryController::class, 'retriggerStock']);
    Route::delete('inventory/product/stock/delete/{inventory_id}', [InventoryController::class, 'inventoryStockDelete']);
    Route::post('inventory/product/return/verify/{inventory_id}', [InventoryController::class, 'inventoryReturnVerify']);
    Route::post('inventory/product/return/received/{uid_inventory}', [InventoryController::class, 'inventoryReturnReceived']);
    Route::post('inventory/product/return/status/{inventory_item_id}', [InventoryController::class, 'updateStatusReceivedVendor']);
    Route::post('inventory/product/return/pre-received/{uid_inventory}', [InventoryController::class, 'inventoryReturnPreReceived']);
    Route::post('inventory/product/return/completed/{uid_inventory}', [InventoryController::class, 'inventoryReturnComplete']);
    Route::post('inventory/product/stock/export_received', [InventoryController::class, 'export_received']);
    Route::post('inventory/product/stock/export_transfer', [InventoryController::class, 'export_transfer']);
    Route::post('inventory/product/stock/export_konsinyasi', [InventoryController::class, 'export_konsinyasi']);
    Route::post('inventory/product/stock/export_return', [InventoryController::class, 'export_return']);
    Route::post('inventory/order-konsinyasi/template', [InventoryController::class, 'orderKonsinyasiTemplate']);
    Route::get('inventory/order-konsinyasi/template/{inventory_id}', [InventoryController::class, 'orderKonsinyasiTemplateItem']);
    Route::post('inventory/order-konsinyasi/download/template', [InventoryController::class, 'orderKonsinyasiDownloadTemplate']);
    Route::post('transfer-konsinyasi/import', [InventoryController::class, 'importOrder']);

    // inventory return
    Route::post('inventory/product/return', [InventoryController::class, 'inventoryReturn']);
    Route::get('inventory/product/return/detail/{inventory_id}', [InventoryController::class, 'inventoryReturnDetail']);
    Route::post('inventory/product/return/save', [InventoryController::class, 'inventoryReturnCreate']);
    Route::post('inventory/product/return/update/{inventory_id}', [InventoryController::class, 'inventoryReturnUpdate']);
    Route::delete('inventory/product/return/delete/{inventory_id}', [InventoryController::class, 'inventoryReturnDelete']);

    // adjustment
    Route::post('inventory/product/adjustment/save', [InventoryController::class, 'inventoryAdjustmentCreate']);
    Route::post('inventory/product/adjustment/process/{inventory_id}', [InventoryController::class, 'inventoryAdjustmentProcess']);
    Route::post('inventory/product/adjustment/approve/{inventory_id}', [InventoryController::class, 'inventoryAdjustmentApprove']);
    Route::post('inventory/product/adjustment/reject/{inventory_id}', [InventoryController::class, 'inventoryAdjustmentReject']);

    // lead master
    Route::post('lead-master', [LeadController::class, 'listLead']);
    Route::get('lead-master/detail/{uid_lead}', [LeadController::class, 'detailLead']);
    Route::post('lead-master/create', [LeadController::class, 'createLead']);
    Route::post('lead-master/update/{uid_lead}', [LeadController::class, 'update']);
    Route::post('lead-master/activity/create', [LeadController::class, 'storeActivity']);
    Route::post('lead-master/activity/update/{activity_id}', [LeadController::class, 'updateActivity']);
    Route::delete('lead-master/activity/delete/{activity_id}', [LeadController::class, 'deleteActivity']);
    Route::post('lead-master/product-needs', [LeadController::class, 'selectProductItems']);
    Route::post('lead-master/product-needs/add', [LeadController::class, 'addProductItem']);
    Route::post('lead-master/product-needs/delete', [LeadController::class, 'deleteProductItem']);
    Route::post('lead-master/product-needs/add-qty', [LeadController::class, 'addQty']);
    Route::post('lead-master/product-needs/remove-qty', [LeadController::class, 'removeQty']);
    Route::post('lead-master/action/reject', [LeadController::class, 'reject']);
    Route::post('lead-master/action/approve', [LeadController::class, 'approve']);
    Route::post('lead-master/action/save-negotiation', [LeadController::class, 'saveNegotiation']);
    Route::post('lead-master/export', [LeadController::class, 'export']);

    Route::post('gp-customer/list', [GPCustomerController::class, 'customerList']);
    Route::post('gp-customer/create', [GPCustomerController::class, 'createCustomer']);
    Route::post('gp-customer/update/{customer_id}', [GPCustomerController::class, 'updateCustomer']);
    Route::delete('gp-customer/delete/{customer_id}', [GPCustomerController::class, 'deleteCustomer']);


    // agent location
    Route::get('domain', [AgentLocationController::class, 'listAgentDomain']);
    Route::get('district/{prov_id}', [AgentLocationController::class, 'listDistrictByProvince']);
    Route::get('subdistrict/{district_id}', [AgentLocationController::class, 'listSubdistrictByDistrict']);
    Route::get('user/province', [AgentLocationController::class, 'listProvinceByUser']);
    Route::get('user/subdistrict/{subdistrict_id}', [AgentLocationController::class, 'listUserBySubdistrict']);

    // product
    Route::prefix('product-management')->group(function () {
        // product
        Route::post('product', [ProductMasterController::class, 'listProductMaster']);
        Route::post('product/status/{product_id}', [ProductMasterController::class, 'updateStatusProductMaster']);
        Route::get('product/{product_id}', [ProductMasterController::class, 'getDetailProductMaster']);
        Route::post('product/save', [ProductMasterController::class, 'saveProductMaster']);
        Route::post('product/save/{product_id}', [ProductMasterController::class, 'updateProductMaster']);
        Route::post('product/set-stock/{product_id}', [ProductMasterController::class, 'updateStockProduct']);
        Route::delete('product/delete/{product_id}', [ProductMasterController::class, 'deleteProductMaster']);
        Route::delete('product/images/delete/{product_images_id}', [ProductMasterController::class, 'handleDeleteProductImages']);
        Route::post('product/export', [ProductMasterController::class, 'export']);

        // product variant
        Route::post('product-variant', [ProductVariantController::class, 'listProductVariant']);
        Route::post('product-variant/status/{product_variant_id}', [ProductVariantController::class, 'updateStatusProductVariant']);
        Route::get('product-variant/detail/{product_variant_id?}', [ProductVariantController::class, 'getDetailProductVariant']);
        Route::post('product-variant/save', [ProductVariantController::class, 'saveProductVariant']);
        Route::post('product-variant/save/{product_variant_id}', [ProductVariantController::class, 'updateProductVariant']);
        Route::post('product-variant/export', [ProductVariantController::class, 'export']);
        Route::post('product-variant/export-base-inventory', [ProductVariantController::class, 'exportBaseInventory']);
        Route::delete('product-variant/delete/{product_variant_id}', [ProductVariantController::class, 'deleteProductVariant']);
        Route::delete('product-variant-bundling/delete/{product_bundling_id}', [ProductVariantController::class, 'deleteProductBundling']);

        // product margin bottom
        Route::post('margin-bottom', [ProductMarginBottom::class, 'listMarginBottom']);
        Route::get('margin-bottom/detail/{product_margin_id?}', [ProductMarginBottom::class, 'getDetailMarginBottom']);
        Route::post('margin-bottom/save', [ProductMarginBottom::class, 'saveMarginBottom']);
        Route::post('margin-bottom/save/{product_margin_id}', [ProductMarginBottom::class, 'updateMarginBottom']);
        Route::delete('margin-bottom/delete/{product_margin_id}', [ProductMarginBottom::class, 'deleteMarginBottom']);

        // product Comment Rating
        Route::post('comment-rating', [ProductCommentRatingController::class, 'listCommentRating']);

        // import
        Route::post('import/list', [ImportController::class, 'listImport']);
        Route::post('import/save', [ImportController::class, 'saveImport']);
        Route::post('import/convert', [ImportController::class, 'saveConvert']);
        Route::post('import/discard', [ImportController::class, 'discardImport']);

        // convert
        Route::post('convert/list', [ConvertController::class, 'listConvert']);
        Route::post('convert/detail/{convert_id}', [ConvertController::class, 'listConvertDetail']);
        Route::post('convert/export/{convert_id}', [ConvertController::class, 'export']);
        Route::post('convert/export/detail/{convert_id}', [ConvertController::class, 'exportConvert']);
    });

    //vendor
    Route::post('vendor/export', [ConvertController::class, 'exportVendor']);
    Route::post('po-receiving/export', [ConvertController::class, 'exportPoReceiving']);

    // stock movement
    Route::post('stock-movement', [StockMovementController::class, 'listStockMovement']);
    Route::post('stock-movement/export', [StockMovementController::class, 'export']);

    // prospect
    Route::prefix('prospect')->group(function () {
        Route::get('list/{status?}', [ProspectController::class, 'index']);
        Route::get('detail/{id}', [ProspectController::class, 'show']);
        Route::get('tags', [ProspectController::class, 'prospectTags']);
        Route::post('create', [ProspectController::class, 'createProspect']);
        Route::get('activity/list', [ProspectController::class, 'getAllActivityProspect']);
        Route::post('activity/list/{id}', [ProspectController::class, 'getProspectActivity']);
        Route::post('activity/create', [ProspectController::class, 'createProspectActivity']);
        Route::post('activity/update/{id}', [ProspectController::class, 'updateProspectActivity']);
        Route::post('update/{id}', [ProspectController::class, 'updatedProspect']);
        Route::post('delete/{id}', [ProspectController::class, 'deleteProspect']);
    });

    // activity
    Route::prefix('activity')->group(function () {
        Route::get('list', [ActivityController::class, 'index']);
        Route::get('detail/{id}', [ActivityController::class, 'show']);
        Route::post('create', [ActivityController::class, 'createActivity']);
        Route::post('update/{id}', [ActivityController::class, 'updatedActivity']);
    });

    Route::prefix('contact')->group(function () {
        Route::get('list', [V1ContactController::class, 'getContactList']);
        Route::post('create', [V1ContactController::class, 'createContact']);
        Route::post('update/{contact_id}', [V1ContactController::class, 'createUpdate']);
    });

    Route::prefix('marketplace')->group(function () {
        Route::post('list', [MarketPlaceController::class, 'listOrder']);
        Route::get('detail/{order_id}', [MarketPlaceController::class, 'listOrderDetail']);
        Route::post('import', [MarketPlaceController::class, 'importOrder']);
    });

    // purchase
    Route::prefix('purchase')->group(function () {
        // purchase order
        Route::post('purchase-order', [PurchaseOrderController::class, 'listPurchaseOrder']);
        Route::post('purchase-order-accurate', [PurchaseOrderAccurateController::class, 'listPurchaseOrder']);
        Route::post('purchase-order-accurate-sync', [PurchaseOrderAccurateController::class, 'syncPurchaseOrderAccurate']);
        Route::post('purchase-order-accurate/save', [PurchaseOrderAccurateController::class, 'savePurchaseOrder']);
        Route::get('purchase-order-accurate/{purchase_order_id}', [PurchaseOrderAccurateController::class, 'detailPurchaseOrder']);
        Route::get('purchase-order/{purchase_order_id}', [PurchaseOrderController::class, 'detailPurchaseOrder']);
        Route::post('purchase-order/update-tax/{purchase_order_id}', [PurchaseOrderController::class, 'updatePurchaseTax']);
        Route::post('purchase-order/save', [PurchaseOrderController::class, 'savePurchaseOrder']);
        Route::post('purchase-order/generate', [PurchaseOrderController::class, 'generatePurchaseOrder']);
        Route::post('purchase-order/generate-bulk', [PurchaseOrderController::class, 'generateBulkPurchaseOrder']);
        Route::post('purchase-order/save/{purchase_order_id}', [PurchaseOrderController::class, 'updatePurchaseOrder']);
        Route::delete('purchase-order/cancel/{purchase_order_id}', [PurchaseOrderController::class, 'cancelPurchaseOrder']);
        Route::post('purchase-order/reject/{purchase_order_id}', [PurchaseOrderController::class, 'rejectPurchaseOrder']);
        Route::post('purchase-order/approve/{purchase_order_id}', [PurchaseOrderController::class, 'approvePurchaseOrder']);
        Route::post('purchase-order/assign-warehouse/{purchase_order_id}', [PurchaseOrderController::class, 'assignToWarehouse']);
        Route::post('purchase-order/status/update/{purchase_order_id}', [PurchaseOrderController::class, 'updateStatusPurchaseOrder']);
        Route::post('purchase-order/billing/save/{purchase_order_id}', [PurchaseOrderController::class, 'billingSave']);
        Route::post('purchase-order/billing/approve/{purchase_billing_id}', [PurchaseOrderController::class, 'billingApprove']);
        Route::post('purchase-order/billing/reject/{purchase_billing_id}', [PurchaseOrderController::class, 'billingReject']);
        Route::post('purchase-order/complete/{purchase_billing_id}', [PurchaseOrderController::class, 'purchaseOrderComplete']);
        Route::post('purchase-order/product/update/{purchase_order_item_id}', [PurchaseOrderController::class, 'updateProductItem']);
        Route::delete('purchase-order/product/delete/{purchase_order_item_id}', [PurchaseOrderController::class, 'deleteProductItem']);
        Route::post('purchase-order/product/invoice', [PurchaseOrderController::class, 'invoiceProductItem']);
        Route::post('purchase-order/product/add/{purchase_order_id}', [PurchaseOrderController::class, 'addProductItem']);
        Route::post('purchase-order/export', [PurchaseOrderController::class, 'export']);
        Route::post('purchase-order/update/do_number/{purchase_order_id}', [PurchaseOrderController::class, 'updateDoNumber']);
        Route::post('purchase-order/submitGp', [PurchaseOrderController::class, 'submitGp']);

        // purchase requititionØØ
        Route::post('purchase-requitition', [PurchaseRequisitionController::class, 'listPurchaseRequitition']);
        Route::get('purchase-requitition/complete', [PurchaseRequisitionController::class, 'getPrComplete']);
        Route::get('purchase-requitition/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'detailPurchaseRequitition']);
        Route::post('purchase-requitition/save', [PurchaseRequisitionController::class, 'createRequitition']);
        Route::post('purchase-requitition/save/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'updatePurchaseRequitition']);
        Route::delete('purchase-requitition/cancel/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'cancelPurchaseRequitition']);
        Route::post('purchase-requitition/reject/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'rejectPurchaseRequitition']);
        Route::post('purchase-requitition/approve/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'approvePurchaseRequitition']);
        Route::post('purchase-requitition/approval/status/{approval_id}', [PurchaseRequisitionController::class, 'approvalVerification']);
        Route::post('purchase-requitition/approval/price/{item_id}', [PurchaseRequisitionController::class, 'updatePrice']);
        Route::post('purchase-requitition/complete/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'purchaseOrderComplete']);
        Route::post('purchase-requitition/attachment/upload/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'uploadAttachment']);
        Route::post('purchase-requitition/attachment/delete/{purchase_requitition_id}', [PurchaseRequisitionController::class, 'deleteAttachment']);
        Route::post('purchase-requitition/export', [PurchaseRequisitionController::class, 'export']);


        Route::post('invoice-entry', [PurchaseInvoiceEntryController::class, 'listPurchaseInvoiceEntry']);
        Route::get('invoice-entry/get/rcv-number', [PurchaseInvoiceEntryController::class, 'listPurchaseInvoiceEntryGetRcvNumber']);
        Route::get('invoice-entry/get/po-number/{vendor_code}', [PurchaseInvoiceEntryController::class, 'getPurchaseOrderByVendorCode']);
        Route::get('invoice-entry/{purchase_order_id}', [PurchaseInvoiceEntryController::class, 'detailPurchaseInvoiceEntry']);
        Route::get('invoice-entry/dashboard/status', [PurchaseInvoiceEntryController::class, 'getStatusCard']);
        Route::post('invoice-entry/save', [PurchaseInvoiceEntryController::class, 'createInvoiceEntry']);
        Route::post('invoice-entry/update/status', [PurchaseInvoiceEntryController::class, 'updateStatusInvoiceEntry']);
        Route::post('invoice-entry/save/{purchase_order_id}', [PurchaseInvoiceEntryController::class, 'updatePurchaseInvoiceEntry']);
        Route::post('invoice-entry/add-billing', [PurchaseInvoiceEntryController::class, 'updateBillingInvoiceEntry']);
        Route::post('invoice-entry/cancel', [PurchaseInvoiceEntryController::class, 'cancelPurchaseInvoiceEntry']);
        Route::post('invoice-entry/export', [PurchaseInvoiceEntryController::class, 'exportPurchaseInvoiceEntry']);
        Route::post('invoice-entry/submit-gp', [PurchaseInvoiceEntryController::class, 'submitGP']);
        Route::post('payment-entry/submit-gp', [PurchaseInvoiceEntryController::class, 'submitPaymentGP']);
    });

    // asset control
    Route::post('asset-control', [AssetController::class, 'listAsset']);
    Route::get('asset-control/{id}', [AssetController::class, 'detailAsset']);
    Route::post('asset-control/save/{id}', [AssetController::class, 'updateAsset']);
    Route::get('asset-control/bulk/print', [AssetController::class, 'bulkPrint']);

    // setting
    Route::prefix('setting')->group(function () {
        // notification template
        Route::post('notification-template', [NotificationTemplateController::class, 'listNotificationTemplate']);
        Route::get('notification-template/{template_id}', [NotificationTemplateController::class, 'getDetailNotificationTemplate']);
        Route::post('notification-template/save', [NotificationTemplateController::class, 'saveNotificationTemplate']);
        Route::post('notification-template/save/{template_id}', [NotificationTemplateController::class, 'updateNotificationTemplate']);
        Route::post('notification-template/status/{template_id}', [NotificationTemplateController::class, 'updateStatusGroup']);
        Route::post('notification-template/export/{group_id?}', [NotificationTemplateController::class, 'export']);
        Route::delete('notification-template/delete/{template_id}', [NotificationTemplateController::class, 'deleteNotificationTemplate']);
        Route::get('notification-template/group/{group_id}', [NotificationTemplateController::class, 'getGroup'])->name('spa.setting-notification-template.group');
    });

    // transaction
    Route::prefix('transaction')->group(function () {
        Route::post('list', [TransactionController::class, 'listTransaction']);
        Route::post('export', [TransactionController::class, 'export']);
        Route::post('new-order', [TransactionController::class, 'createNewOrder']);
        Route::post('popaket/order', [TransactionController::class, 'createOrderPopaket']);
        Route::post('new-order/status', [TransactionController::class, 'updateStatusLink']);
        Route::post('cancel', [TransactionController::class, 'postCancelOrder']);
        Route::post('payment/{type}', [TransactionController::class, 'verifyPayment']);
        Route::post('bulk/print/invoice', [TransactionController::class, 'printInvoice']);
        Route::post('bulk/print/label', [TransactionController::class, 'printLabel']);
        Route::post('bulk/ready-to-ship', [TransactionController::class, 'readyToShip']);
        Route::post('bulk/asign-to-warehouse', [TransactionController::class, 'asignToWarehouse']);
        Route::post('track', [TransactionController::class, 'trackOrder']);
        Route::post('apply-voucher', [TransactionController::class, 'applyVoucher']);
        Route::get('detail/{transaction_id}', [TransactionController::class, 'getTransactionDetail']);
        Route::get('user/{user_id}', [TransactionController::class, 'getTransactionUser']);
        Route::get('detail/agent/{transaction_id}', [TransactionController::class, 'getTransactionDetailAgent']);
        Route::post('withdraw/bagi-hasil/export', [TransactionController::class, 'exportBagiHasil']);
        Route::post('bank/payment/virtual-account', [TransactionController::class, 'getVANumber']);
        Route::post('update/price-item/{item_id}', [TransactionController::class, 'updatePrice']);
        Route::post('update/discount-item/{item_id}', [TransactionController::class, 'updateDiscount']);
        Route::post('update/admin-fee/{item_id}', [TransactionController::class, 'updateAdminFee']);
        Route::post('update/deduction/{item_id}', [TransactionController::class, 'updateDeduction']);
        Route::post('update/resi', [TransactionController::class, 'updateResi']);
        Route::post('update/address', [TransactionController::class, 'updateAddressTransaction']);
    });

    // barcode
    Route::prefix('barcode')->group(function () {
        Route::post('list', [BarcodeController::class, 'listBarcode']);
        Route::get('detail/{barcode_id}', [BarcodeController::class, 'getBarcodeDetail']);
        Route::get('reset/{id}', [BarcodeController::class, 'reset']);
        Route::post('upload', [BarcodeController::class, 'uploadImage']);
    });

    // ahli gizi
    Route::post('comission-withdraw', [CommisionWithdrawController::class, 'listCommisionWithdraw']);
    Route::get('comission-withdraw/detail/{commision_withdraw_id?}', [CommisionWithdrawController::class, 'getDetailCommisionWithdraw']);
    Route::post('comission-withdraw/save', [CommisionWithdrawController::class, 'saveCommisionWithdraw']);
    Route::post('comission-withdraw/save/{commision_withdraw_id}', [CommisionWithdrawController::class, 'updateCommisionWithdraw']);
    Route::post('comission-withdraw/cancel/{commision_withdraw_id}', [CommisionWithdrawController::class, 'cancelCommisionWithdraw']);
    Route::post('comission-withdraw/approve/{commision_withdraw_id}', [CommisionWithdrawController::class, 'approveCommisionWithdraw']);
    Route::post('comission-withdraw/reject/{commision_withdraw_id}', [CommisionWithdrawController::class, 'rejectCommisionWithdraw']);
    Route::post('comission-withdraw/export', [CommisionWithdrawController::class, 'export']);

    Route::prefix('accurate')->group(function () {
        // Customer
        Route::get('/customer', [AccurateController::class, 'customerJson']);
        Route::get('/customers/{id}/doh', [AccurateController::class, 'customerDohJson']);
        Route::post('/sync-customer', function () {
            try {
                Artisan::call('accurate:import-customers');

                DB::connection('pgsql')->table('accurate_sync_logs')->updateOrInsert(
                    ['type' => 'customer'],
                    [
                        'sync_date'   => now(),
                        'created_by'  => Auth::user()?->id ?? 'system',
                        'created_at'  => now(),
                    ]
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Customer berhasil disinkronisasi dari Accurate.'
                ]);
            } catch (\Throwable $e) {
                dump('Gagal sinkronisasi Accurate: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal sinkronisasi. Silakan cek console atau periksa server.',
                ], 500);
            }
        });


        // Product
        Route::get('/product', [AccurateController::class, 'productJson']);
        Route::put('/product/{accurate_id}/switch', [AccurateController::class, 'switchStatus']);
        Route::post('/sync-product', function () {
            try {
                Artisan::call('accurate:import-items');

                DB::connection('pgsql')->table('accurate_sync_logs')->updateOrInsert(
                    ['type' => 'product'],
                    [
                        'sync_date'   => now(),
                        'created_by'  => Auth::user()?->id ?? 'system',
                        'created_at'  => now(),
                    ]
                );

                return response()->json([
                    'status' => 'success',
                    'message' => 'Product berhasil disinkronisasi dari Accurate.'
                ]);
            } catch (\Throwable $e) {
                dump('Gagal sinkronisasi Accurate: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal sinkronisasi. Silakan cek console atau periksa server.',
                ], 500);
            }
        });


        // Warehouse
        Route::get('/warehouse', [AccurateController::class, 'warehouseJson']);
        Route::post('/sync-warehouse', function () {
            Artisan::call('accurate:import-warehouses');
            DB::connection('pgsql')->table('accurate_sync_logs')->updateOrInsert(
                ['type' => 'warehouse'],
                [
                    'sync_date'   => now(),
                    'created_by'  => Auth::user()?->id ?? 'system',
                    'created_at'  => now(),
                ]
            );
            return response()->json([
                'status' => 'success',
                'message' => 'Warehouse berhasil disinkronisasi dari Accurate.'
            ]);
        });

        Route::get('/last-sync-warehouse', function () {
            $lastSync = DB::connection('pgsql')->table('accurate_sync_logs')
                ->where('type', 'warehouse')
                ->orderByDesc('sync_date')
                ->first();

            return response()->json([
                'status' => 'success',
                'last_synced_at' => $lastSync?->sync_date,
            ]);
        });

        Route::get('/merchandiser', [AccurateController::class, 'merchandiserJson']);
        Route::get('/merchandiser/{user}/stores', [AccurateController::class, 'getMerchandiserStores']);
        Route::post('/merchandiser/{user}/stores', [AccurateController::class, 'updateMerchandiserStores']);
        Route::get('/stores', [AccurateController::class, 'storeJson']);
        Route::get('/stores/{user}', [AccurateController::class, 'storeMDJson']);
        Route::get('/store/{customerNo}', [AccurateController::class, 'getStoreInfo']);

        // Sales Order
        Route::get('/sales-order', [AccurateController::class, 'salesOrderJson']);
        Route::get('/sales-order/{id}/details', [AccurateController::class, 'salesOrderDetailJson']);

        // Sales Order App
        Route::get('/sales-order-app', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'getSalesOrders']);
        Route::get('/sales-order-app/{id}', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'getSalesOrderDetail']);

        // Sales Order App V2 (with approval feature)
        Route::prefix('v2/sales-order-app')->group(function () {
            Route::get('/', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'getSalesOrdersV2']);
            Route::get('/{id}', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'getSalesOrderDetailV2']);
            Route::put('/{id}', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'updateSalesOrderV2']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'approveSalesOrder']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'rejectSalesOrder']);
            Route::post('/{id}/update-items', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'updateItems']);
        });

        // Sales Invoice
        Route::get('/sales-invoice', [AccurateController::class, 'salesInvoiceJson']);

        // Item Transfer
        Route::get('/item-transfer', [AccurateController::class, 'itemTransferJson']);
        Route::get('/item-transfer/{id}/details', [AccurateController::class, 'itemTransferDetailJson']);
        Route::post('/sync-item-transfer', function () {
            try {
                Artisan::call('accurate:import-item-transfer');
                return response()->json([
                    'status' => 'success',
                    'message' => 'STO berhasil disinkronisasi dari Accurate.'
                ]);
            } catch (\Throwable $e) {
                dump('Gagal sinkronisasi Accurate: ' . $e->getMessage());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal sinkronisasi. Silakan cek console atau periksa server.',
                ], 500);
            }
        });

        // Sales Returns
        Route::get('/sales-returns', [AccurateController::class, 'salesReturnJson']);
        Route::post('/sync-sales-returns', function () {
            Artisan::call('accurate:import-sales-returns');
            return response()->json([
                'status' => 'success',
                'message' => 'Data Sales Return berhasil disinkronkan.'
            ]);
        });

        // Stock Opname
        Route::get('/stock-opname/{type}/data', [AccurateController::class, 'stockOpnameJson']);
        Route::get('/stock-opname/group-data', [AccurateController::class, 'groupOpname']);
        Route::get('/stock-opname/group-data/{id}', function ($id) {
            $customers = DB::connection('pgsql')
                ->table('accurate_stock_opnames as aso')
                ->join('contact_group_customer as cgc', 'aso.customer_code_sub', '=', 'cgc.customer_no')
                ->where('aso.customer_code', $id)
                ->select([
                    'cgc.id',
                    'cgc.customer_name',
                    'cgc.customer_no',
                ])
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $customers,
            ]);
        });
        Route::get('/stock-opname/{id}/detail', [AccurateController::class, 'stockOpnameDetailJson']);
        Route::post('/stock-opname/import', [AccurateController::class, 'importStockOpname']);

        // Stock System Calculated
        Route::get('/stock-system-calculated', [AccurateController::class, 'stockSystemCalculatedJson']);
        Route::get('/stock-comparison', [AccurateController::class, 'stockComparisonJson']);
        Route::post('/stock-comparison/filter', [AccurateController::class, 'filterComparison']);
        Route::get('/stock-comparison/tmp', [AccurateController::class, 'stockComparisonJson']);
        Route::post('/stock-comparison/export', [AccurateController::class, 'stockComparisonExport']);

        Route::post('/stock-system-calculate', [AccurateController::class, 'startStockCalculation']);
        Route::get('/stock-system-calc-status/{jobId}', [AccurateController::class, 'stockCalculationStatus']);


        // Contact Group (under accurate scope)
        Route::post('/contact-group', [AccurateController::class, 'contactGroupJson']);
        Route::post('/contact-group/delete/{id}', function ($id) {
            DB::connection('pgsql')->table('contact_groups')->where('id', $id)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Contact group berhasil dihapus.'
            ]);
        });
        Route::post('/contact-group/create', [AccurateController::class, 'contactGroupCreate']);

        // DOH Settings for Contact Groups
        Route::get('/contact-group/{contactGroupId}/doh-settings', [AccurateController::class, 'contactGroupDohSettings']);
        Route::post('/contact-group/{contactGroupId}/doh-settings', [AccurateController::class, 'saveContactGroupDohSettings']);
        Route::delete('/contact-group/{contactGroupId}/doh-settings/{settingId}', [AccurateController::class, 'deleteContactGroupDohSetting']);
        Route::get('/contact-group/{id}/customers', function ($id) {
            $group = DB::connection('pgsql')
                ->table('contact_groups')
                ->where('id', $id)
                ->first();

            $customers = DB::connection('pgsql')
                ->table('contact_group_customer as cgc')
                ->leftJoin('accurate_customers as ac', 'ac.customer_no', '=', 'cgc.customer_no')
                ->leftJoin('accurate_stock_opnames as aso', 'cgc.customer_no', '=', 'aso.customer_code_sub')
                ->where('cgc.contact_group_id', $id)
                ->select([
                    DB::raw("COALESCE(ac.customer_no, cgc.customer_no) as customer_no"),
                    DB::raw("COALESCE(ac.name, cgc.customer_name) as customer_name"),
                    'cgc.customer_email',
                    'cgc.customer_telp',
                    'cgc.cut_off',
                    'cgc.prov',
                    'cgc.kab_kota',
                    'cgc.kec',
                    'cgc.jml_hari',
                    DB::raw("MAX(aso.trans_date) as trans_date")
                ])
                ->groupBy([
                    'ac.customer_no',
                    'ac.name',
                    'cgc.customer_no',
                    'cgc.customer_name',
                    'cgc.customer_email',
                    'cgc.customer_telp',
                    'cgc.cut_off',
                    'cgc.prov',
                    'cgc.kab_kota',
                    'cgc.kec',
                    'cgc.jml_hari'
                ])
                ->get();

            return response()->json([
                'status' => 'success',
                'group' => $group,
                'data' => $customers,
            ]);
        });
        Route::post('/contact-group/{id}/import-customers', [AccurateController::class, 'importGroupCustomers']);
        Route::delete('/contact-group/{id}/import-customers/{customer_no}', [AccurateController::class, 'deleteCustomer']);
        Route::put('/contact-group/{id}/import-customers/{customer_no}', [AccurateController::class, 'updateCustomerInGroup']);

        // Actual Stocks
        Route::get('/actual-stocks', [AccurateActualStocksController::class, 'getActualStocks']);
        Route::post('/actual-stocks/import', [AccurateActualStocksController::class, 'importActualStocks']);
        Route::get('/actual-stocks/template', [AccurateActualStocksController::class, 'downloadTemplate']);
        Route::get('/import/template', [AccurateController::class, 'downloadImportTemplate']);

        // Visit Management
        Route::get('/visits', [\App\Http\Controllers\Spa\Accurate\VisitController::class, 'getVisits']);
        Route::get('/visits/by-pic/{picName}', [\App\Http\Controllers\Spa\Accurate\VisitController::class, 'getVisitsByPic']);
        Route::get('/visits/statistics', [\App\Http\Controllers\Spa\Accurate\VisitController::class, 'getVisitStatistics']);
        Route::get('/visits/{visitId}', [\App\Http\Controllers\Spa\Accurate\VisitController::class, 'getVisitDetail']);
        Route::post('/visits', [\App\Http\Controllers\Spa\Accurate\VisitController::class, 'createVisit']);
        Route::put('/visits/{visitId}/status', [\App\Http\Controllers\Spa\Accurate\VisitController::class, 'updateVisitStatus']);
        Route::delete('/visits/{visitId}', [\App\Http\Controllers\Spa\Accurate\VisitController::class, 'deleteVisit']);
    });
});

Route::get('ajax/search/user', [AjaxController::class, 'searchUser']);

// lead master
Route::get('ajax/search/lead/contact', [AjaxController::class, 'searcContactFromLead']);
Route::get('ajax/search/lead/sales', [AjaxController::class, 'searcSalesFromLead']);



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('test-gp', [GpController::class, 'testGp'])->name('test.gp');

Route::post('payment-notifications', [PaymentNotification::class, 'notifications'])->name('notification.payment');
Route::post('ginee-callback', [GineeWebhookController::class, 'webhook'])->name('ginee.callback');
Route::middleware('cors')->group(function () {
    Route::post('update-profile-photo', [UserController::class, 'updateProfilePhoto'])->name('user.update-profile-photo');
    Route::post('email-notification', [SendEmailController::class, 'sendMailNotificationApi'])->name('email-notification');

    Route::prefix('transaction')->group(function () {
        Route::post('confirm-payment', [ConfirmPaymentController::class, 'uploadPayment'])->name('transaction.confirm');
    });
});

Route::get('sync-sc-user/{user_id}', function ($user_id) {
    $user = User::find($user_id);
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }
    return response()->json([
        'status' => 'success',
        'message' => 'Data Sales Return berhasil disinkronkan.',
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'uid' => $user->uid,
            'password' => $user->password,
            'email' => $user->email,
            'telepon' => $user->telepon,
        ]
    ]);
})->middleware('api.key');

// URL Shortener Redirect Routes (outside auth middleware)
Route::get('s/{short_code}', [RedirectController::class, 'redirect'])->name('url.redirect');
Route::get('s/{short_code}/preview', [RedirectController::class, 'preview'])->name('url.preview');

// URL Shortener API for External Redirector (public access)
Route::get('redirector/{short_code}', [UrlShortenerController::class, 'getByShortCode'])->name('api.redirector.get');

Route::get('sync-sc-user/{user_id}', function ($user_id) {
    $user = User::find($user_id);
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }
    return response()->json([
        'status' => 'success',
        'message' => 'Data Sales Return berhasil disinkronkan.',
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'uid' => $user->uid,
            'password' => $user->password,
            'email' => $user->email,
            'role' => $user->role->role_name,
            'telepon' => $user->telepon,
        ]
    ]);
});

Route::get('sync-sc-user-name', function () {
    $user = User::where('name', request()->name)->first();
    if (!request()->name) {
        return response()->json([
            'status' => 'error',
            'message' => 'Name is required'
        ], 404);
    }
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'User not found'
        ], 404);
    }
    return response()->json([
        'status' => 'success',
        'message' => 'Data Sales Return berhasil disinkronkan.',
        'data' => [
            'id' => $user->id,
            'name' => $user->name,
            'uid' => $user->uid,
            'password' => $user->password,
            'email' => $user->email,
            'role' => $user->role->role_name,
            'telepon' => $user->telepon,
        ]
    ]);
});
