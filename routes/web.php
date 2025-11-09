<?php

use App\Events\PaymentSuccessEvent;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Callback\PopaketCallback;
use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\GracePeriodController;
use App\Http\Controllers\Spa\AgentDomainManagementController;
use App\Http\Controllers\Spa\AgentManagementController;
use App\Http\Controllers\Spa\Auth\LoginController;
use App\Http\Controllers\Spa\Auth\RegisterController;
use App\Http\Controllers\Spa\BinController;
use App\Http\Controllers\Spa\Case\ManualController;
use App\Http\Controllers\Spa\Case\RefundController;
use App\Http\Controllers\Spa\Case\ReturnController;
use App\Http\Controllers\Spa\CekQueryController;
use App\Http\Controllers\Spa\CheckoutAgent;
use App\Http\Controllers\Spa\CommisionWithdrawController;
use App\Http\Controllers\Spa\ContactController as SpaContactController;
use App\Http\Controllers\Spa\ContactGroupController;
use App\Http\Controllers\Spa\DashboardController;
use App\Http\Controllers\Spa\GenieController;
use App\Http\Controllers\Spa\MpethixController;
use App\Http\Controllers\Spa\GPCustomerController;
use App\Http\Controllers\Spa\GPSubmissionController;
use App\Http\Controllers\Spa\InventoryController as SpaInventoryController;
use App\Http\Controllers\Spa\LeadController;
use App\Http\Controllers\Spa\Marketplace\MarketPlaceController;
use App\Http\Controllers\Spa\TicketController;
use App\Http\Controllers\Spa\Master\BannerController as MasterBannerController;
use App\Http\Controllers\Spa\Master\BrandController;
use App\Http\Controllers\Spa\Master\UrlShortenerController as MasterUrlShortenerController;
use App\Http\Controllers\Spa\Master\NotifController;
use App\Http\Controllers\Spa\Master\CategoryController as MasterCategoryController;
use App\Http\Controllers\Spa\Master\LogisticController as MasterLogisticController;
use App\Http\Controllers\Spa\Master\MasterPointController as MasterMasterPointController;
use App\Http\Controllers\Spa\Master\PackageController as MasterPackageController;
use App\Http\Controllers\Spa\Master\PaymentMethodController as MasterPaymentMethodController;
use App\Http\Controllers\Spa\Master\VariantController as MasterVariantController;
use App\Http\Controllers\Spa\Master\VoucherController as MasterVoucherController;
use App\Http\Controllers\Spa\Master\PaymentTermController as MasterPaymentTermController;
use App\Http\Controllers\Spa\Master\MasterTaxController as MasterMasterTaxController;
use App\Http\Controllers\Spa\Master\SkuController as MasterSkuController;
use App\Http\Controllers\Spa\Master\WarehouseController as MasterWarehouseController;
use App\Http\Controllers\Spa\Master\MasterDiscountController as MasterMasterDiscountController;
use App\Http\Controllers\Spa\Master\TypeCaseController as MasterTypeCaseController;
use App\Http\Controllers\Spa\Master\CategoryCaseController as MasterCategoryCaseController;
use App\Http\Controllers\Spa\Master\CheckbookController;
use App\Http\Controllers\Spa\Master\CompanyAccountController;
use App\Http\Controllers\Spa\Master\ProductCartonController;
use App\Http\Controllers\Spa\Master\StatusCaseController as MasterStatusCaseController;
use App\Http\Controllers\Spa\Master\PriorityCaseController as MasterPriorityCaseController;
use App\Http\Controllers\Spa\Master\SourceCaseController as MasterSourceCaseController;
use App\Http\Controllers\Spa\Master\LevelController as MasterLevelController;
use App\Http\Controllers\Spa\Master\MasterBatchIDController;
use App\Http\Controllers\Spa\Master\MasterBinController;
use App\Http\Controllers\Spa\Master\MasterOngkirController;
use App\Http\Controllers\Spa\Master\MasterPphController;
use App\Http\Controllers\Spa\Master\MasterSiteIDController;
use App\Http\Controllers\Spa\Master\ProductAdditionalController;
use App\Http\Controllers\Spa\Master\RateLimitSettingController;
use App\Http\Controllers\Spa\Master\SalesChannelController;
use App\Http\Controllers\Spa\Master\VendorController;
use App\Http\Controllers\Spa\MenuController;
use App\Http\Controllers\Spa\Order\GpController;
use App\Http\Controllers\Spa\Order\OrderFreeBiesController;
use App\Http\Controllers\Spa\Order\OrderInvoiceController;
use App\Http\Controllers\Spa\Order\SalesReturnController as OrderSalesReturnController;
use App\Http\Controllers\Spa\OrderLeadController as SpaOrderLeadController;
use App\Http\Controllers\Spa\OrderManualController as SpaOrderManualController;
use App\Http\Controllers\Spa\Order\OrderKonsinyasiController as SpaOrderKonsinyasiController;
use App\Http\Controllers\Spa\ProductManagement\ConvertController;
use App\Http\Controllers\Spa\ProductManagement\ImportController;
use App\Http\Controllers\Spa\ProductManagement\ProductCommentRatingController;
use App\Http\Controllers\Spa\ProductManagement\ProductMarginBottom;
use App\Http\Controllers\Spa\ProductManagement\ProductMasterController;
use App\Http\Controllers\Spa\ProductManagement\ProductVariantController as ProductManagementProductVariantController;
use App\Http\Controllers\Spa\Purchase\PurchaseInvoiceEntryController;
use App\Http\Controllers\Spa\Purchase\PurchaseOrderController;
use App\Http\Controllers\Spa\Purchase\PurchaseOrderAccurateController;
use App\Http\Controllers\Spa\Accurate\AccurateController;
use App\Http\Controllers\Spa\Purchase\PurchaseRequisitionController;
use App\Http\Controllers\Spa\StockMovementController;
use App\Http\Controllers\Spa\Setting\NotificationTemplateController as SettingNotificationTemplateController;
use App\Http\Controllers\Spa\TransactionController as SpaTransactionController;
use App\Http\Controllers\Spa\BarcodeController;
use App\Http\Controllers\Spa\TransAgentController;
use App\Http\Controllers\Spa\AssetController;
use App\Http\Controllers\Spa\UserManagement\RoleController;
use App\Http\Controllers\Webhook\WebhookController;
use App\Http\Livewire\Auth\PasswordReset;
use App\Http\Livewire\CrudGenerator;
use App\Http\Livewire\Dashboard;
use App\Http\Livewire\DashboardAgent;
use App\Http\Livewire\DashboardLead;
// use App\Http\Livewire\Master\CategoryController;
// use App\Http\Livewire\Master\ProductController;
use App\Http\Livewire\Settings\Menu;
use App\Http\Livewire\UserManagement\Permission;
use App\Http\Livewire\UserManagement\PermissionRole;
use App\Http\Livewire\UserManagement\Role;
use App\Http\Livewire\UserManagement\User;
use App\Http\Livewire\CategoryController;
use App\Http\Livewire\Transaction\TransactionController;
use App\Http\Livewire\VariantController;
use App\Http\Livewire\DetailVariantController;
use App\Http\Livewire\Master\PaymentMethodController;
use App\Http\Livewire\ProductController;
use App\Http\Livewire\PackageController;
use App\Http\Livewire\ProductVariantController;
use App\Http\Livewire\BannerController;
use App\Http\Livewire\CompanyController;
use App\Http\Livewire\LevelController;
// use App\Http\Livewire\Master\BrandController;
use App\Http\Livewire\Master\VoucherController;
use App\Http\Livewire\UserDataController;
use App\Http\Livewire\PriceController;
use App\Http\Livewire\WarehouseController;
use App\Http\Livewire\ShippingMethodController;
use App\Http\Livewire\MasterPointController;
use App\Http\Livewire\BusinessEntityController;
use App\Http\Livewire\Setting\NotificationTemplateController;
use App\Http\Livewire\SettingDescriptionController;
use App\Http\Livewire\CommentRatingController;
use App\Http\Livewire\NotificationController;
use App\Http\Livewire\Setting\GeneralSettingController;
use App\Http\Livewire\ContactController;
use App\Http\Livewire\LeadMasterController;
use App\Http\Livewire\MarginBottomController;
use App\Http\Livewire\FaqCategoryController;
use App\Http\Livewire\FaqContentController;
use App\Http\Livewire\FaqSubmenuController;
use App\Http\Livewire\OrderLeadController;
use App\Http\Livewire\OrderManualController;
use App\Http\Livewire\PaymentTermController;
use App\Http\Livewire\MasterTaxController;
use App\Http\Livewire\MasterDiscountController;
use App\Http\Livewire\TypeCaseController;
use App\Http\Livewire\CategoryCaseController;
use App\Http\Livewire\StatusCaseController;
use App\Http\Livewire\PriorityCaseController;
use App\Http\Livewire\SourceCaseController;
use App\Http\Livewire\CaseController;
use App\Http\Livewire\SkuMasterController;
use App\Http\Livewire\RefundMasterController;
use App\Http\Livewire\ReturMasterController;
use App\Http\Livewire\SalesReturnController;
use Illuminate\Support\Facades\Route;

//agent
use App\Http\Livewire\Agent\ProductController as ProductAgent;
use App\Http\Livewire\Agent\CartController;
use App\Http\Livewire\Agent\OrderController;
use App\Http\Livewire\Agent\TransactionSuccess;
use App\Http\Livewire\AgentManagement\AgentDetailController;
use App\Http\Livewire\AgentManagement\DomainController;
use App\Http\Livewire\InventoryController;
use App\Http\Livewire\Master\LogisticController;
use App\Http\Livewire\Master\LogisticRateController;
use App\Http\Livewire\Product\ListConvert;
use App\Http\Livewire\Product\ProductSKUConvert;
use App\Http\Livewire\Product\ProductSkuImport;
use App\Http\Livewire\Shipping\ShippingVoucher;
use App\Http\Livewire\Transaction\TransactionReportController;
use App\Jobs\TestQueue;
use App\Jobs\UpdatePriceQueue;
use App\Models\ContactGroup;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductVariantStock;
use App\Models\SalesChannel;
use App\Models\TeamUser;
use App\Models\Role as RoleModel;
use App\Models\User as ModelsUser;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Spa\Accurate\AccurateActualStocksController;
/*
|--------------------------------------------------------------------------
| Spa Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => ['auth:sanctum', 'user.authorization']], function () {
    Route::get('genie/dashboard', [GenieController::class, 'index'])->name('spa.genie.dashboard');
    Route::get('genie/order/list', [GenieController::class, 'index'])->name('spa.genie.index');
    Route::get('genie/order/detail/{orderId?}', [GenieController::class, 'index'])->name('spa.genie.detail');

    //Ethix MP
    Route::get('mp-ethix/dashboard', [MpethixController::class, 'index'])->name('spa.mp-ethix.index');
    Route::get('mp-ethix/detail/{orderId}', [MpethixController::class, 'index'])->name('spa.mp-ethix.detail');

    // contact
    Route::get('contact', [SpaContactController::class, 'index'])->name('spa.contact.index');
    Route::get('contact/create', [SpaContactController::class, 'index'])->name('spa.contact.create');
    Route::get('contact/detail/{user_id?}', [SpaContactController::class, 'index'])->name('spa.contact.detail');
    Route::get('contact/update/{user_id?}', [SpaContactController::class, 'index'])->name('spa.contact.update');

    // contact group
    Route::get('contact-group', [ContactGroupController::class, 'index'])->name('spa.contact-group.index');
    Route::get('contact-group/detail/{group_id?}', [ContactGroupController::class, 'index'])->name('spa.contact-group.detail');
    Route::get('contact-group/form/{group_id?}', [ContactGroupController::class, 'index'])->name('spa.contact-group.update');

    // transaction agent
    Route::get('trans-agent/all-trans', [TransAgentController::class, 'index'])->name('spa.transAgent.index');
    Route::get('trans-agent/waiting-payment', [TransAgentController::class, 'index'])->name('spa.transAgent.waiting-payment');
    Route::get('trans-agent/confirmation', [TransAgentController::class, 'index'])->name('spa.transAgent.confimation');
    Route::get('trans-agent/new-transaction', [TransAgentController::class, 'index'])->name('spa.transAgent.new-transaction');
    Route::get('trans-agent/warehouse', [TransAgentController::class, 'index'])->name('spa.transAgent.warehouse');
    Route::get('trans-agent/ready-product', [TransAgentController::class, 'index'])->name('spa.transAgent.ready-product');
    Route::get('trans-agent/delivery', [TransAgentController::class, 'index'])->name('spa.transAgent.delivery');
    Route::get('trans-agent/order-accepted', [TransAgentController::class, 'index'])->name('spa.transAgent.order-accepted');
    Route::get('trans-agent/history', [TransAgentController::class, 'index'])->name('spa.transAgent.history');
    Route::get('trans-agent/detail/{id}', [TransAgentController::class, 'index'])->name('spa.transAgent.detail');

    // checkout agent
    Route::get('cart/list', [CheckoutAgent::class, 'index'])->name('spa.cart.index');

    // order lead
    Route::get('order/order-lead', [SpaOrderLeadController::class, 'index'])->name('spa.order-lead.index');
    Route::get('order/order-lead/detail/{uid_lead}', [SpaOrderLeadController::class, 'index'])->name('spa.order-lead.detail');
    Route::get('order/order-lead/form/{uid_lead?}', [SpaOrderLeadController::class, 'index'])->name('spa.order-lead.form');

    // order lead manual
    Route::get('order/order-manual', [SpaOrderManualController::class, 'index'])->name('spa.order-lead-manual.index');
    Route::get('order/order-manual/detail/{uid_lead}', [SpaOrderManualController::class, 'index'])->name('spa.order-lead-manual.detail');
    Route::get('order/order-manual/form/{uid_lead?}', [SpaOrderManualController::class, 'index'])->name('spa.order-lead-manual.form');

    // order lead manual
    Route::get('order/freebies', [OrderFreeBiesController::class, 'index'])->name('spa.freebies.index');
    Route::get('order/freebies/detail/{uid_lead}', [OrderFreeBiesController::class, 'index'])->name('spa.freebies.detail');
    Route::get('order/freebies/form/{uid_lead?}', [OrderFreeBiesController::class, 'index'])->name('spa.freebies.form');

    // order lead manual
    Route::get('order/invoice', [OrderInvoiceController::class, 'invoiceIndex'])->name('spa.invoice-so.index');
    Route::get('order/invoice/detail/{uid_lead}/{uid_delivery}', [OrderInvoiceController::class, 'invoiceIndex'])->name('spa.invoice-so.detail');
    Route::get('order/invoice/form/{uid_lead}', [OrderInvoiceController::class, 'invoiceIndex'])->name('spa.invoice-so.form');

    // order konsinyasi
    Route::get('order/order-konsinyasi', [SpaOrderKonsinyasiController::class, 'index'])->name('spa.order-konsinyasi.index');
    Route::get('order/order-konsinyasi/detail/{uid_lead}', [SpaOrderKonsinyasiController::class, 'index'])->name('spa.order-konsinyasi.detail');
    Route::get('order/order-konsinyasi/form/{uid_lead?}', [SpaOrderKonsinyasiController::class, 'index'])->name('spa.order-konsinyasi.form');

    // order submit
    Route::get('order/submit/history', [GpController::class, 'submitIndex'])->name('spa.submit-history.index');
    Route::get('order/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-detail.index');

    // sales order
    Route::get('order/sales-order/detail/{uid_lead}', [SpaOrderManualController::class, 'index'])->name('spa.order-sales.detail');

    // ethix submit
    Route::get('ethix/submit/history', [GpController::class, 'submitIndex'])->name('spa.submit-history-ethix.index');
    Route::get('ethix/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-ethix-detail.index');


    // purchase submit
    Route::get('purchase/history-submit', [GpController::class, 'submitIndex'])->name('spa.submit-history-purchase.index');
    Route::get('purchase/history-submit/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-purchase-detail.index');

    // marketplace submit
    Route::get('marketplace/submit/history', [GpController::class, 'submitIndex'])->name('spa.submit-history-marketplace.index');
    Route::get('marketplace/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-marketplace-detail.index');

    // submit-ethix submit
    Route::get('ethix/submit/history', [GpController::class, 'submitIndex'])->name('spa.submit-history-submit-ethix.index');
    Route::get('ethix/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-submit-ethix-detail.index');

    // submit-ethix submit
    Route::get('contact-import/submit/history', [GpController::class, 'submitIndex'])->name('spa.contact-import.index');
    Route::get('contact-import/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.contact-import-detail.index');


    // tranaction submit history
    Route::get('transaction-telmart/submit/history', [GpController::class, 'submitIndex'])->name('spa.submit-history-transaction-telmart.index');
    Route::get('transaction-telmart/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-transaction-telmart-detail.index');


    // tranaction submit history
    Route::get('transaction/submit/history', [GpController::class, 'submitIndex'])->name('spa.submit-history-transaction.index');
    Route::get('transaction/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-transaction-detail.index');

    // tranaction agent submit history
    Route::get('transaction-agent/submit/history', [GpController::class, 'submitIndex'])->name('spa.submit-history-transaction-agent.index');
    Route::get('transaction-agent/submit/history/{submit_id}', [GpController::class, 'submitIndex'])->name('spa.submit-history-transaction-agent-detail.index');

    // dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // agent management
    Route::get('/agent/list', [AgentManagementController::class, 'index'])->name('spa.agent-management.index');
    Route::get('/agent/domain', [AgentDomainManagementController::class, 'index'])->name('spa.agent-domain.index');

    // menu
    Route::get('/menu', [MenuController::class, 'index'])->name('spa.menu.index');

    // case section
    // return
    Route::get('case/return', [ReturnController::class, 'index'])->name('spa.return.index');
    Route::get('case/return/{uid_retur}', [ReturnController::class, 'index'])->name('spa.return.detail');

    // refund
    Route::get('case/refund', [RefundController::class, 'index'])->name('spa.refund.index');
    Route::get('case/refund/{uid_refund}', [RefundController::class, 'index'])->name('spa.refund.detail');

    // manual
    Route::get('case/manual', [ManualController::class, 'index'])->name('spa.manual.index');
    Route::get('case/manual/{uid_case}', [ManualController::class, 'index'])->name('spa.manual.detail');
    Route::get('case/manual/form/{uid_case?}', [ManualController::class, 'index'])->name('spa.manual.form');
    Route::get('case/manual/detail/{uid_case?}', [ManualController::class, 'index'])->name('spa.manual-page.detail');

    // sales return
    Route::get('order/sales-return', [OrderSalesReturnController::class, 'index'])->name('spa.sales-return.index');
    Route::get('order/sales-return/form/{uid_return?}', [OrderSalesReturnController::class, 'index'])->name('spa.sales-return.form');
    Route::get('order/sales-return/detail/{uid_return?}', [OrderSalesReturnController::class, 'index'])->name('spa.sales-return.detail');

    Route::get('inventory-new', [SpaInventoryController::class, 'index'])->name('spa.inventory.index');
    Route::get('inventory-new/inventory-product-stock', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.index');
    Route::get('inventory-new/inventory-product-stock/form', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.add');
    Route::get('inventory-new/inventory-product-stock/detail/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.update');
    // transfer
    Route::get('inventory-new/inventory-product-transfer/form', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.transfer.form');
    Route::get('inventory-new/inventory-product-transfer/form/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.transfer');
    Route::get('inventory-new/inventory-product-transfer/detail/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.transfer.detail');
    Route::get('inventory-new/inventory-product-transfer', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.transfer.index');
    // konsinyasi
    Route::get('inventory-new/item-transfer-konsinyasi', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.konsinyasi.index');
    Route::get('inventory-new/item-transfer-konsinyasi/form', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.konsinyasi.form');
    Route::get('inventory-new/item-transfer-konsinyasi/form/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.konsinyasi');
    Route::get('inventory-new/item-transfer-konsinyasi/detail/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.konsinyasi.detail');
    // adjustment
    Route::get('stock-adjustment', [SpaInventoryController::class, 'index'])->name('spa.inventory.stock.adjustment.index');
    Route::get('stock-adjustment/form', [SpaInventoryController::class, 'index'])->name('spa.inventory.stock.adjustment.form');
    Route::get('stock-adjustment/form/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.stock.adjustment');
    Route::get('stock-adjustment/detail/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.stock.adjustment.detail');

    Route::get('inventory-new/inventory-product-return/form/{inventory_id?}', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.return');
    Route::get('inventory-new/inventory-product-return/detail/{inventory_id}', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.return.detail');
    Route::get('inventory-new/inventory-product-return', [SpaInventoryController::class, 'index'])->name('spa.inventory.product.return.index');

    Route::get('lead-master', [LeadController::class, 'index'])->name('spa.lead-master.index');
    Route::get('lead-master/detail/{uid_lead}', [LeadController::class, 'index'])->name('spa.lead-master.detail');
    Route::get('lead-master/form/{uid_lead?}', [LeadController::class, 'index'])->name('spa.lead-master.form');

    Route::get('ticket', [TicketController::class, 'index'])->name('spa.ticket-master.index');
    Route::get('ticket/detail/{id}', [TicketController::class, 'index'])->name('spa.ticket-master.detail');


    Route::get('gp-submission', [GPSubmissionController::class, 'index'])->name('spa.gp.index');
    Route::get('gp-submission/list/detail/{list_id}', [GPSubmissionController::class, 'index'])->name('spa.gp.detail');
    Route::get('gp-submission/export/{item_id}', [GPSubmissionController::class, 'exportConvert'])->name('spa.gp.export');

    Route::get('master/gp-customer-code', [GPCustomerController::class, 'index'])->name('spa.gp-customer.index');

    // master data
    // brand
    Route::get('master/brand', [BrandController::class, 'index'])->name('spa.master-brand.index');
    Route::get('master/brand/form/{brand_id?}', [BrandController::class, 'index'])->name('spa.master-brand-form.index');

    // company account
    Route::get('master/company-account', [CompanyAccountController::class, 'index'])->name('spa.master-company-account.index');
    Route::get('master/company-account/form/{company_account_id?}', [CompanyAccountController::class, 'index'])->name('spa.master-company-account-form.index');

    // product carton
    Route::get('master/produk-karton', [ProductCartonController::class, 'index'])->name('spa.master-product-carton.index');
    Route::get('master/produk-karton/form/{product_carton_id?}', [ProductCartonController::class, 'index'])->name('spa.master-product-carton-form.index');

    // banner
    Route::get('master/banner', [MasterBannerController::class, 'index'])->name('spa.master-banner.index');
    Route::get('master/banner/form/{banner_id?}', [MasterBannerController::class, 'index'])->name('spa.master-banner-form.index');

    // url shortener
    Route::get('master/url-shortener', [MasterUrlShortenerController::class, 'index'])->name('spa.master-url-shortener.index');
    Route::get('master/url-shortener/form/{url_shortener_id?}', [MasterUrlShortenerController::class, 'index'])->name('spa.master-url-shortener-form.index');

    // category
    Route::get('master/category', [MasterCategoryController::class, 'index'])->name('spa.master-category.index');
    Route::get('master/category/form/{category_id?}', [MasterCategoryController::class, 'index'])->name('spa.master-category-form.index');

    // point
    Route::get('master/point', [MasterMasterPointController::class, 'index'])->name('spa.master-point.index');
    Route::get('master/point/form/{master_point_id?}', [MasterMasterPointController::class, 'index'])->name('spa.master-point-form.index');

    // package
    Route::get('master/package', [MasterPackageController::class, 'index'])->name('spa.master-package.index');
    Route::get('master/package/form/{package_id?}', [MasterPackageController::class, 'index'])->name('spa.master-package-form.index');


    // payment-method
    Route::get('master/payment-method', [MasterPaymentMethodController::class, 'index'])->name('spa.master-payment-method.index');
    Route::get('master/payment-method/form/{payment_method_id?}', [MasterPaymentMethodController::class, 'index'])->name('spa.master-payment-method-form.index');


    // shiping-method
    Route::get('master/online-logistic', [MasterLogisticController::class, 'index'])->name('spa.master-shipping-method.index');
    Route::get('master/offline-logistic', [MasterLogisticController::class, 'index'])->name('spa.master-shipping-method-offline.index');

    // variant
    Route::get('master/variant', [MasterVariantController::class, 'index'])->name('spa.master-variant.index');
    Route::get('master/variant/form/{payment_method_id?}', [MasterVariantController::class, 'index'])->name('spa.master-variant-form.index');

    // voucher
    Route::get('master/voucher', [MasterVoucherController::class, 'index'])->name('spa.master-voucher.index');
    Route::get('master/voucher/form/{payment_method_id?}', [MasterVoucherController::class, 'index'])->name('spa.master-voucher-form.index');

    // payment term
    Route::get('master/payment-term', [MasterPaymentTermController::class, 'index'])->name('spa.master-payment-term.index');
    Route::get('master/payment-term/form/{payment_method_id?}', [MasterPaymentTermController::class, 'index'])->name('spa.master-payment-term-form.index');

    // master tax
    Route::get('master/tax', [MasterMasterTaxController::class, 'index'])->name('spa.master-master-tax.index');
    Route::get('master/tax/form/{master_tax_id?}', [MasterMasterTaxController::class, 'index'])->name('spa.master-master-tax-form.index');

    // vendor
    Route::get('master/vendor', [VendorController::class, 'index'])->name('spa.vendor.index');
    Route::get('master/vendor/form/{id?}', [VendorController::class, 'index'])->name('spa.vendor-form.index');

    // checkbook
    Route::get('master/checkbook', [CheckbookController::class, 'index'])->name('spa.checkbook.index');
    Route::get('master/checkbook/form/{id?}', [CheckbookController::class, 'index'])->name('spa.checkbook-form.index');

    // master site id
    Route::get('master/site-id', [MasterSiteIDController::class, 'index'])->name('spa.master-siteId.index');
    Route::get('master/site-id/form/{master_site_id?}', [MasterSiteIDController::class, 'index'])->name('spa.master-siteId-form.index');

    // master batch id
    Route::get('master/batch-id', [MasterBatchIDController::class, 'index'])->name('spa.master-batchId.index');
    Route::get('master/batch-id/form/{master_batch_id?}', [MasterBatchIDController::class, 'index'])->name('spa.master-batchId-form.index');

    // master pph
    Route::get('master/master-pph', [MasterPphController::class, 'index'])->name('spa.master-master-pph.index');
    Route::get('master/master-pph/form/{master_pph_id?}', [MasterPphController::class, 'index'])->name('spa.master-master-pph-form.index');

    // sku
    Route::get('master/sku', [MasterSkuController::class, 'index'])->name('spa.master-sku.index');
    Route::get('master/sku/form/{sku_id?}', [MasterSkuController::class, 'index'])->name('spa.master-sku-form.index');

    // warehouse
    Route::get('master/warehouse', [MasterWarehouseController::class, 'index'])->name('spa.master-warehouse.index');
    Route::get('master/warehouse/form/{warehouse_id?}', [MasterWarehouseController::class, 'index'])->name('spa.master-warehouse-form.index');

    // bin
    Route::get('master/bin', [MasterBinController::class, 'index'])->name('spa.master-bin.index');
    Route::get('master/bin/form/{master_bin_id?}', [MasterBinController::class, 'index'])->name('spa.master-bin-form.index');

    // master discount
    Route::get('master/master-discount', [MasterMasterDiscountController::class, 'index'])->name('spa.master-master-discount.index');
    Route::get('master/master-discount/form/{master_discount_id?}', [MasterMasterDiscountController::class, 'index'])->name('spa.master-master-discount-form.index');

    // type case
    Route::get('master/type-case', [MasterTypeCaseController::class, 'index'])->name('spa.master-type-case.index');
    Route::get('master/type-case/form/{type_case_id?}', [MasterTypeCaseController::class, 'index'])->name('spa.master-type-case-form.index');

    // category type case
    Route::get('master/category-type-case', [MasterCategoryCaseController::class, 'index'])->name('spa.master-category-type-case.index');
    Route::get('master/category-type-case/form/{category_type_case_id?}', [MasterCategoryCaseController::class, 'index'])->name('spa.master-category-type-case-form.index');

    // status case
    Route::get('master/status-case', [MasterStatusCaseController::class, 'index'])->name('spa.master-status-case.index');
    Route::get('master/status-case/form/{status_case_id?}', [MasterStatusCaseController::class, 'index'])->name('spa.master-status-case-form.index');

    // priority case
    Route::get('master/priority-case', [MasterPriorityCaseController::class, 'index'])->name('spa.master-priority-case.index');
    Route::get('master/priority-case/form/{priority_case_id?}', [MasterPriorityCaseController::class, 'index'])->name('spa.master-priority-case-form.index');

    // source case
    Route::get('master/source-case', [MasterSourceCaseController::class, 'index'])->name('spa.master-source-case.index');
    Route::get('master/source-case/form/{source_case_id?}', [MasterSourceCaseController::class, 'index'])->name('spa.master-source-case-form.index');

    // level
    Route::get('master/level-price', [MasterSourceCaseController::class, 'index'])->name('spa.master-level.index');
    Route::get('master/level-price/form/{source_case_id?}', [MasterSourceCaseController::class, 'index'])->name('spa.master-level-form.index');

    // pengemasan
    Route::get('master/pengemasan', [ProductAdditionalController::class, 'index'])->name('spa.master-pengemasan.index');
    Route::get('master/pengemasan/form/{product_additional_id?}', [ProductAdditionalController::class, 'index'])->name('spa.master-pengemasan-form.index');

    // perlengkapan
    Route::get('master/perlengkapan', [ProductAdditionalController::class, 'index'])->name('spa.master-perlengkapan.index');
    Route::get('master/perlengkapan/form/{product_additional_id?}', [ProductAdditionalController::class, 'index'])->name('spa.master-perlengkapan-form.index');

    // sales-channel
    Route::get('master/sales-channel', [SalesChannelController::class, 'index'])->name('spa.master-sales-channel.index');
    Route::get('master/sales-channel/form/{sales_channel_id?}', [SalesChannelController::class, 'index'])->name('spa.master-sales-channel-form.index');

    // ongkir
    Route::get('master/ongkir', [MasterOngkirController::class, 'index'])->name('spa.master-ongkir.index');
    Route::get('master/ongkir/form/{sales_channel_id?}', [MasterOngkirController::class, 'index'])->name('spa.master-ongkir-form.index');

    // notif alert
    Route::get('master/rate-limit', [RateLimitSettingController::class, 'index'])->name('spa.rate-limit.index');

    // notif alert
    Route::get('master/notification', [NotifController::class, 'index'])->name('spa.master-notif.index');
    Route::get('master/notification/form/{notif_id?}', [NotifController::class, 'index'])->name('spa.master-notif-form.index');

    // marketplace
    Route::get('marketplace/list', [MarketPlaceController::class, 'index'])->name('spa.marketplace.index');
    Route::get('marketplace/detail/{order_id?}', [MarketPlaceController::class, 'index'])->name('spa.marketplace-detail.index');

    // site management
    Route::prefix('site-management')->group(function () {
        // role
        Route::get('role', [RoleController::class, 'index'])->name('spa.site-management-role.index');
        Route::get('permission', [Permission::class, 'index'])->name('spa.site-management-permission.index');
        Route::get('role/form/{role_id?}', [RoleController::class, 'index'])->name('spa.site-management-role-form.index');
    });

    // product management
    Route::prefix('product-management')->group(function () {
        // product master
        Route::get('product', [ProductMasterController::class, 'index'])->name('spa.product-master.index');
        Route::get('product/form/{product_id?}', [ProductMasterController::class, 'index'])->name('spa.product-master-form.index');
        Route::get('product/stock-allocation/{product_id?}', [ProductMasterController::class, 'index'])->name('spa.product-master-stock.index');

        // product variant
        Route::get('product-variant', [ProductManagementProductVariantController::class, 'index'])->name('spa.product-variant.index');
        Route::get('product-variant/form/{product_id?}', [ProductManagementProductVariantController::class, 'index'])->name('spa.product-variant-form.index');

        // product variant
        Route::get('margin-bottom', [ProductMarginBottom::class, 'index'])->name('spa.margin-bottom.index');
        Route::get('margin-bottom/form/{product_id?}', [ProductMarginBottom::class, 'index'])->name('spa.margin-bottom-form.index');

        // product comment & rating
        Route::get('comment-rating', [ProductCommentRatingController::class, 'index'])->name('spa.comment-rating.index');

        // import product
        Route::get('import-product', [ImportController::class, 'index'])->name('spa.import-product.index');

        // convert product
        Route::get('convert-product', [ConvertController::class, 'index'])->name('spa.convert-product.index');
        Route::get('convert-product/detail/{convert_id}', [ConvertController::class, 'index'])->name('spa.convert-product-detail.index');
    });

    // setting
    Route::prefix('setting')->group(function () {
        // template notification
        Route::get('notification-template/group', [SettingNotificationTemplateController::class, 'index'])->name('spa.setting-notification-template.group');
        Route::get('notification-template/list/{group_id?}', [SettingNotificationTemplateController::class, 'index'])->name('spa.setting-notification-template.index');
        Route::get('notification-template/form/{group_id}/{template_id?}', [SettingNotificationTemplateController::class, 'index'])->name('spa.setting-notification-template.form');
    });

    // asset controls
    Route::get('asset-control', [AssetController::class, 'index'])->name('spa.asset.index');
    Route::get('asset-control/print/{id?}', [AssetController::class, 'print'])->name('spa.asset.print');
    Route::get('asset-control/form/{id?}', [AssetController::class, 'index'])->name('spa.asset.detail');

    // purchase order
    Route::get('purchase/purchase-order', [PurchaseOrderController::class, 'index'])->name('spa.purchase-purchase-order.index');
    Route::get('purchase/purchase-order/form/{purchase_order_id?}', [PurchaseOrderController::class, 'index'])->name('spa.purchase-purchase-order-form.index');
    Route::get('purchase/purchase-order/detail/{purchase_order_id?}', [PurchaseOrderController::class, 'index'])->name('spa.purchase-purchase-order-form.detail');
    Route::get('purchase/purchase-order/print/{purchase_order_id?}', [PurchaseOrderController::class, 'exportPdf'])->name('spa.purchase-purchase-order-export.index');

    // Purchase order accurate
    Route::get('purchase/purchase-order-accurate', [PurchaseOrderAccurateController::class, 'index'])->name('spa.purchase-purchase-order-accurate.index');
    Route::get('purchase/purchase-order-accurate/detail/{purchase_order_id?}', [PurchaseOrderAccurateController::class, 'index'])->name('spa.purchase-purchase-order-accurate-form.detail');

    // purchase invoice entry
    Route::get('purchase/invoice-entry', [PurchaseInvoiceEntryController::class, 'index'])->name('spa.purchase-invoice-entry.index');
    Route::get('purchase/invoice-entry/form/{purchase_invoice_entry_id?}', [PurchaseInvoiceEntryController::class, 'index'])->name('spa.purchase-invoice-entry-form.index');
    Route::get('purchase/invoice-entry/detail/{purchase_invoice_entry_id?}', [PurchaseInvoiceEntryController::class, 'index'])->name('spa.purchase-invoice-entry-form.detail');
    // Route::get('purchase/invoice-entry/print/{purchase_invoice_entry_id?}', [PurchaseInvoiceEntryController::class, 'exportPdf'])->name('spa.purchase-invoice-entry-export.index');

    // purchase requisition
    Route::get('purchase/purchase-requisition', [PurchaseRequisitionController::class, 'index'])->name('spa.purchase-purchase-requisition.index');
    Route::get('purchase/purchase-requisition/form/{purchase_requisition_id?}', [PurchaseRequisitionController::class, 'index'])->name('spa.purchase-purchase-requisition-form.index');
    Route::get('purchase/purchase-requisition/detail/{purchase_requisition_id?}', [PurchaseRequisitionController::class, 'index'])->name('spa.purchase-purchase-requisition-form.detail');


    // stock movemant
    Route::get('stock-movement', [StockMovementController::class, 'index'])->name('spa.stock-movement.index');

    // bin
    Route::get('bin/list', [BinController::class, 'index'])->name('spa.list-bin.index');
    Route::get('bin/detail/{bin_id}', [BinController::class, 'index'])->name('spa.bin.detail');

    // Generate Barcode
    Route::get('barcode/list', [BinController::class, 'index'])->name('spa.barcode.index');
    Route::get('barcode/detail/{barcode_id}', [BinController::class, 'index'])->name('spa.barcode.detail');

    // transaction
    Route::get('transaction/waiting-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction.waiting-payment');
    Route::get('transaction/waiting-confirmation', [SpaTransactionController::class, 'index'])->name('spa.transaction.waiting-confirmation');
    Route::get('transaction/confirm-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction.confirm-payment');
    Route::get('transaction/on-process', [SpaTransactionController::class, 'index'])->name('spa.transaction.on-process');
    Route::get('transaction/ready-to-ship', [SpaTransactionController::class, 'index'])->name('spa.transaction.ready-to-ship');
    Route::get('transaction/on-delivery', [SpaTransactionController::class, 'index'])->name('spa.transaction.on-delivery');
    Route::get('transaction/delivered', [SpaTransactionController::class, 'index'])->name('spa.transaction.delivered');
    Route::get('transaction/returned', [SpaTransactionController::class, 'index'])->name('spa.transaction.returned');
    Route::get('transaction/cancelled', [SpaTransactionController::class, 'index'])->name('spa.transaction.cancelled');
    Route::get('transaction/new-order', [SpaTransactionController::class, 'index'])->name('spa.transaction.new-order');
    Route::get('transaction/report-transaction', [SpaTransactionController::class, 'index'])->name('spa.transaction.report.data');
    Route::get('transaction/detail/new-order/{id}', [SpaTransactionController::class, 'index'])->name('spa.transaction.new-order.detail');
    Route::get('transaction/detail/{id}', [SpaTransactionController::class, 'index'])->name('spa.transaction.detail');

    // transaction telmart
    Route::get('transaction-telmart/waiting-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.waiting-payment');
    Route::get('transaction-telmart/waiting-confirmation', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.waiting-confirmation');
    Route::get('transaction-telmart/confirm-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.confirm-payment');
    Route::get('transaction-telmart/on-process', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.on-process');
    Route::get('transaction-telmart/ready-to-ship', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.ready-to-ship');
    Route::get('transaction-telmart/on-delivery', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.on-delivery');
    Route::get('transaction-telmart/delivered', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.delivered');
    Route::get('transaction-telmart/returned', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.returned');
    Route::get('transaction-telmart/cancelled', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.cancelled');
    Route::get('transaction-telmart/new-order', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.new-order');
    Route::get('transaction-telmart/report-transaction', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.report.data');
    Route::get('transaction-telmart/cancelled', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.cancelled');
    Route::get('transaction-telmart/detail/new-order/{id}', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.new-order.detail');
    Route::get('transaction-telmart/detail/{id}', [SpaTransactionController::class, 'index'])->name('spa.transaction_telmart.detail');

    // transaction lms
    Route::get('transaction-lms/waiting-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.waiting-payment');
    Route::get('transaction-lms/waiting-confirmation', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.waiting-confirmation');
    Route::get('transaction-lms/confirm-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.confirm-payment');
    Route::get('transaction-lms/on-process', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.on-process');
    Route::get('transaction-lms/ready-to-ship', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.ready-to-ship');
    Route::get('transaction-lms/on-delivery', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.on-delivery');
    Route::get('transaction-lms/delivered', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.delivered');
    Route::get('transaction-lms/returned', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.returned');
    Route::get('transaction-lms/cancelled', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.cancelled');
    Route::get('transaction-lms/new-order', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.new-order');
    Route::get('transaction-lms/report-transaction', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.report.data');
    Route::get('transaction-lms/cancelled', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.cancelled');
    Route::get('transaction-lms/detail/new-order/{id}', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.new-order.detail');
    Route::get('transaction-lms/detail/{id}', [SpaTransactionController::class, 'index'])->name('spa.transaction_lms.detail');

    // transaction agent
    Route::get('transaction-agent/new-order', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.new-order');
    Route::get('transaction-agent/waiting-confirmation', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.waiting-confirmation');
    Route::get('transaction-agent/on-delivery', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.on-delivery');
    Route::get('transaction-agent/delivered', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.delivered');
    Route::get('transaction-agent/completed', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.completed');
    Route::get('transaction-agent/cancelled', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.cancelled');
    Route::get('transaction-agent/report-transaction', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.report.data');
    Route::get('transaction-agent/sales-invoice', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.sales-invoice');
    Route::get('transaction-agent/detail/{id}', [SpaTransactionController::class, 'index'])->name('spa.transaction_agent.detail');

    // barcode
    Route::get('barcode/on-production', [BarcodeController::class, 'index'])->name('spa.barcode.on-production');
    Route::get('barcode/inbound', [BarcodeController::class, 'index'])->name('spa.barcode.inbound');
    Route::get('barcode/transfer', [BarcodeController::class, 'index'])->name('spa.barcode.transfer');
    Route::get('barcode/outbound', [BarcodeController::class, 'index'])->name('spa.barcode.outbound');

    // agent
    Route::get('transaction/agent/waiting-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.waiting-payment');
    Route::get('transaction/agent/waiting-confirmation', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.waiting-confirmation');
    Route::get('transaction/agent/confirm-payment', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.confirm-payment');
    Route::get('transaction/agent/on-process', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.on-process');
    Route::get('transaction/agent/ready-to-ship', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.ready-to-ship');
    Route::get('transaction/agent/on-delivery', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.on-delivery');
    Route::get('transaction/agent/delivered', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.delivered');
    Route::get('transaction/agent/cancelled', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.cancelled');
    Route::get('transaction/agent/{transaction_id}', [SpaTransactionController::class, 'index'])->name('spa.transaction.agent.detail');

    // transaction agent2
    Route::get('transaction/agent', [PurchaseRequisitionController::class, 'index'])->name('spa.transaction-agent.index');
    // Route::get('transaction/agent/{transaction_id}', [PurchaseRequisitionController::class, 'index'])->name('spa.transaction-agent.detail');

    // ahli gizi
    Route::get('comission-withdraw', [CommisionWithdrawController::class, 'index'])->name('spa.comission-withdraw.index');
    Route::get('comission-withdraw/detail', [CommisionWithdrawController::class, 'index'])->name('spa.comission-withdraw-detail.index');
    Route::get('comission-withdraw/form/{commision_withdraw_id?}', [CommisionWithdrawController::class, 'index'])->name('spa.comission-withdraw-form.index');
});

Route::get('purchase/purchase-requitition/print/{purchase_requisition_id}', [PurchaseRequisitionController::class, 'exportPdf'])->name('spa.purchase-purchase-requisition-export.index');
Route::get('purchase/purchase-requitition/print-nostamp/{purchase_requisition_id}', [PurchaseRequisitionController::class, 'exportPdfNoStamp'])->name('spa.purchase-purchase-requisition-export-nostamp.index');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('login/dashboard');
});
Route::get('logout-user', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
})->name('logout.user');

Route::get('/debug-sentry', function () {
    throw new Exception('My first Sentry error!');
});

Route::get('/cek-event', function () {
    broadcast(new PaymentSuccessEvent(["status" => 'oK']));
});

// cek query
Route::get('cek-query', [CekQueryController::class, 'cekQuery']);

// callback
Route::prefix('callback')->group(function () {
    Route::post('popaket-tracking', [PopaketCallback::class, 'getCallbackTracking'])->name('callback.popaket.tracking');
    Route::post('popaket-resi', [PopaketCallback::class, 'getCallbackAwb'])->name('callback.popaket.resi');
});
// callback
Route::prefix('product')->group(function () {
    Route::get('import', ProductSkuImport::class)->name('product.import');
    Route::get('convert', ListConvert::class)->name('product.convert.list');
    Route::get('convert/detail/{id}', ProductSKUConvert::class)->name('product.convert');
});

Route::get('login/dashboard', [LoginController::class, 'index'])->name('dashboard.login');
Route::get('agent/register', [RegisterController::class, 'index'])->name('dashboard.register');

Route::post('login', [AuthController::class, 'login'])->name('admin.login');
Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.forgot.password');
Route::get('/auth/reset-password/{token?}', PasswordReset::class)->name('reset.password');
Route::get('/transaction-list/report', ReportController::class)->name('transaction.report');
Route::get('/invoice/{transaction_id}', [InvoiceController::class, 'printInvoice'])->name('invoice.print');
Route::get('/invoice/agent/{transaction_id}', [InvoiceController::class, 'printInvoiceAgent'])->name('invoice.print.agent');
Route::get('/print/{transaction_id}', [InvoiceController::class, 'printPdf'])->name('invoice.pdf');
Route::get('/bulk/{transaction_id}', [InvoiceController::class, 'printBulkStructInvoice'])->name('invoice.bulk.pdf');
Route::get('/invoice/struct/{transaction_id}', [InvoiceController::class, 'printStructInvoice'])->name('invoice.struct.print');
Route::get('/invoice-agent/struct/{transaction_id}', [InvoiceController::class, 'printStructInvoice'])->name('invoice.struct.print.agent');

Route::group(['middleware' => ['auth:sanctum', 'verified', 'user.authorization']], function () {
    // Crud Generator Route
    Route::get('/crud-generator', CrudGenerator::class)->name('crud.generator');

    // Route::prefix('/site-management')->group(function () {
    //     Route::get('/permission', Permission::class)->name('permission');
    //     Route::get('/permission-role/{role_id}', PermissionRole::class)->name('permission.role');
    //     // Route::get('/role', Role::class)->name('role');

    //     Route::get('/menu', Menu::class)->name('menu');
    // });

    // App Route
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard-agent', DashboardAgent::class)->name('dashboard.agent');
    Route::get('/dashboard-lead', DashboardLead::class)->name('dashboard.lead');

    // Master data
    Route::prefix('/master')->group(function () {
        // Route::get('/brand', BrandController::class)->name('brand');
        Route::get('/company', CompanyController::class)->name('company');
        // Route::get('/category', CategoryController::class)->name('category');
        // Route::get('/variant', VariantController::class)->name('variant');
        Route::get('/detail-variant', DetailVariantController::class)->name('detail-variant');
        Route::get('/product', ProductController::class)->name('product');
        // Route::get('/voucher', VoucherController::class)->name('voucher');
        // Route::get('/package', PackageController::class)->name('package');
        // Route::get('/payment-method', PaymentMethodController::class)->name('payment-method');
        // Route::get('/banner', BannerController::class)->name('banner');
        // Route::get('/level', LevelController::class)->name('level');
        Route::get('/business-entity', BusinessEntityController::class)->name('business-entity');
        // Route::get('/shipping-method', ShippingMethodController::class)->name('shipping-method');
        Route::get('/logistic', LogisticController::class)->name('logistic');
        Route::get('/logistic-rate-service', LogisticRateController::class)->name('logistic.rate.service');
        // Route::get('/point', MasterPointController::class)->name('master-point');
        Route::get('/lead', LeadMasterController::class)->name('lead-master');
        // Route::get('/payment-term', PaymentTermController::class)->name('payment-term');
        // Route::get('/tax', MasterTaxController::class)->name('tax');
        // Route::get('/discount', MasterDiscountController::class)->name('discount');
        // Route::get('/sku-master', SkuMasterController::class)->name('sku-master');
        Route::get('/refund', RefundMasterController::class)->name('refund-master');
        Route::get('/retur', ReturMasterController::class)->name('retur-master');
        Route::get('/sales-return', SalesReturnController::class)->name('sr-master');
        //cases
        // Route::get('/type-case', TypeCaseController::class)->name('type-case');
        // Route::get('/category-case', CategoryCaseController::class)->name('category-case');
        // Route::get('/status-case', StatusCaseController::class)->name('status-case');
        // Route::get('/priority-case', PriorityCaseController::class)->name('priority-case');
        // Route::get('/source-case', SourceCaseController::class)->name('source-case');
        Route::get('/case', CaseController::class)->name('cases');
    });

    Route::get('/order-lead', OrderLeadController::class)->name('order-lead');
    Route::get('/order-manual', OrderManualController::class)->name('order-manual');
    // Route::get('/warehouse', WarehouseController::class)->name('warehouse');

    Route::get('/shipping-voucher', ShippingVoucher::class)->name('shipping-voucher');

    Route::prefix('/transactions')->group(function () {
        Route::group([], function () {
            Route::get('/lists', TransactionController::class)->name('transaction.list');
            Route::get('/report', TransactionReportController::class)->name('transaction.report.data');
            // role finance
            Route::get('/waiting-confirm', TransactionController::class)->name('transaction.waiting-confirm');
            Route::get('/confirm-payment', TransactionController::class)->name('transaction.confirm-payment');
            // role warehouse
            Route::get('/process', TransactionController::class)->name('transaction.process');
            Route::get('/delivery', TransactionController::class)->name('transaction.delivery');
            Route::get('/delivered', TransactionController::class)->name('transaction.delivered');
            Route::get('/on-process', TransactionController::class)->name('transaction.on-process');
            Route::get('/history', TransactionController::class)->name('transaction.history');
            Route::get('/siap-dikirim', TransactionController::class)->name('transaction.siap-dikirim');
            // role admin
            Route::get('/waiting-payment', TransactionController::class)->name('transaction.waiting-payment');
            Route::get('/approve-finance', TransactionController::class)->name('transaction.approve-finance');
            Route::get('/admin-process', TransactionController::class)->name('transaction.admin-process');
        });

        // agent proccess
        Route::prefix('/agent-proccess')->group(function () {
            Route::get('/lists', TransactionController::class)->name('transaction.agent-proccess.list');
            // role finance
            Route::get('/waiting-confirm', TransactionController::class)->name('transaction.agent-proccess.waiting-confirm');
            Route::get('/confirm-payment', TransactionController::class)->name('transaction.agent-proccess.confirm-payment');
            // role warehouse
            Route::get('/process', TransactionController::class)->name('transaction.agent-proccess.process');
            Route::get('/delivery', TransactionController::class)->name('transaction.agent-proccess.delivery');
            Route::get('/delivered', TransactionController::class)->name('transaction.agent-proccess.delivered');
            Route::get('/on-process', TransactionController::class)->name('transaction.agent-proccess.on-process');
            Route::get('/history', TransactionController::class)->name('transaction.agent-proccess.history');
            Route::get('/siap-dikirim', TransactionController::class)->name('transaction.agent-proccess.siap-dikirim');
            // role admin
            Route::get('/waiting-payment', TransactionController::class)->name('transaction.agent-proccess.waiting-payment');
            Route::get('/approve-finance', TransactionController::class)->name('transaction.agent-proccess.approve-finance');
            Route::get('/admin-process', TransactionController::class)->name('transaction.agent-proccess.admin-process');
        });

        Route::prefix('/agent')->group(function () {
            // agent role
            Route::get('/waiting-agent', TransactionController::class)->name('transaction.waiting-agent');
            Route::get('/approve-agent', TransactionController::class)->name('transaction.approve-agent');
            Route::get('/proccess-agent', TransactionController::class)->name('transaction.agent-process');
            Route::get('/history-agent', TransactionController::class)->name('transaction.agent-history');
        });
    });

    Route::prefix('/product')->group(function () {
        Route::get('/variant', ProductVariantController::class)->name('product-variant');
        Route::get('/price', PriceController::class)->name('price');
        Route::get('/agent', ProductAgent::class)->name('product-agent');
        Route::get('/comment-rating', CommentRatingController::class)->name('comment-rating');
    });

    Route::prefix('/notification')->group(function () {
        Route::get('/notification', NotificationController::class)->name('notification');
        Route::get('/notification/read-all', [NotificationController::class, 'readAllNotif'])->name('notification.read-all');
    });
    Route::prefix('/agent-management')->group(function () {
        Route::get('/all', AgentDetailController::class)->name('agent.all');
        Route::get('/domain', DomainController::class)->name('domain.all');
    });

    // role agent
    Route::get('/cart', CartController::class)->name('cart');
    Route::get('/transaction/{transaction_id?}', TransactionSuccess::class)->name('transaction.detail');
    Route::get('/order', OrderController::class)->name('order');
    // Route::get('/contact', ContactController::class)->name('contact');

    Route::get('/user-management', UserDataController::class)->name('customer-management');

    Route::group(['prefix' => 'setting'], function () {
        // Route::get('/notification-template', NotificationTemplateController::class)->name('notification.template');
        Route::get('/general-setting', GeneralSettingController::class)->name('general.setting');
        Route::get('/setting-description', SettingDescriptionController::class)->name('setting-description');
        Route::get('/user', User::class)->name('user');
    });

    Route::get('/inventory', InventoryController::class)->name('inventory');

    Route::get('/margin-bottom', MarginBottomController::class)->name('margin-bottom');

    Route::get('/faq-submenu', FaqSubmenuController::class)->name('faq-submenu');
    Route::get('/faq-category', FaqCategoryController::class)->name('faq-category');
    Route::get('/faq-content', FaqContentController::class)->name('faq-content');
    // file: ///519bba57-ec11-4ca8-ade5-50783b8216d3

    Route::get('/print/sj/{uid_lead}/{delivery_id?}', [PrintController::class, 'printSj'])->name('print.sj');
    Route::get('/print/so/{uid_lead}', [PrintController::class, 'printSo'])->name('print.so');
    Route::get('/print/sok/{uid_inventory}', [PrintController::class, 'printSoKons'])->name('print.sok');
    Route::get('/print/sik/{uid_inventory}', [PrintController::class, 'printSiKons'])->name('print.sik');
    Route::get('/print/sjk/{uid_inventory}', [PrintController::class, 'printSjKons'])->name('print.sjk');
    Route::get('/print/adjust/{uid_inventory}', [PrintController::class, 'printAdjust'])->name('print.adjust');

    Route::get('/print/sr/{uid_lead?}', [PrintController::class, 'printSr'])->name('print.sr');
    Route::get('/print/si/{uid_lead?}/{product_need_ids?}', [PrintController::class, 'printSi'])->name('print.si');
    Route::get('/print/spr/{uid_inventory}', [PrintController::class, 'printSpr'])->name('print.spr');
    Route::get('/print/label/{id_transaksi}', [PrintController::class, 'printLabelTransaction'])->name('print.label');
    // Route::get('/print/invoice', [PrintController::class, 'printSr'])->name('print.invoice');
    Route::get('/print/invoice/{uid_lead}', [PrintController::class, 'printInvoice'])->name('print.invoice');
    Route::get('/print/transfer/{uid_inventory}', [PrintController::class, 'printTransfer'])->name('print.transfer');

    Route::get('/check_duedate', [GracePeriodController::class, 'check_duedate'])->name('check_duedate');

    Route::prefix('/accurate-integration')->group(function () {
        Route::get('/customer', [AccurateController::class, 'index'])->name('spa.accurate-integration-customer.index');

        Route::get('/product', [AccurateController::class, 'index'])->name('spa.accurate-integration-product.index');
        Route::get('/warehouse', [AccurateController::class, 'index'])->name('spa.accurate-integration-warehouse.index');
        Route::get('/merchandiser', [AccurateController::class, 'index'])->name('spa.accurate-integration-merchandiser.index');
        Route::get('/list-merchandiser', [AccurateController::class, 'index'])->name('spa.accurate-integration-list-merchandiser.index');
        Route::get('/list-merchandiser/{id}', [AccurateController::class, 'index'])->name('spa.accurate-integration-list-merchandiser.detail');
        Route::get('/store-stock-count/{storeId}/{id}', [AccurateController::class, 'index'])->name('spa.accurate-integration-store.index');

        Route::get('/sales-order', [AccurateController::class, 'index'])->name('spa.accurate-integration-sales-order.index');
        Route::get('/sales-order-app', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'index'])->name('spa.accurate-integration-sales-order-app.index');
        Route::get('/sales-order-app/{id}', [\App\Http\Controllers\Spa\Accurate\SalesOrderController::class, 'index'])->name('spa.accurate-integration-sales-order-app.detail');
        Route::get('/sales-invoice', [AccurateController::class, 'index'])->name('spa.accurate-integration-sales-invoice.index');
        Route::get('/stock-transfer', [AccurateController::class, 'index'])->name('spa.accurate-integration-stock-transfer.index');
        Route::get('/sales-return', [AccurateController::class, 'index'])->name('spa.accurate-integration-sales-return.index');
        Route::get('/sales-return-import', [AccurateController::class, 'index'])->name('spa.accurate-integration-sales-return-import.index');
        Route::get('/stock-system-calculated', [AccurateController::class, 'index'])->name('spa.accurate-stock-system-calculated.index');
        Route::get('/stock-system-accurate', [AccurateController::class, 'index'])->name('spa.accurate-stock-system-accurate.index');
        Route::get('/contact-group', [AccurateController::class, 'index'])->name('spa.accurate-contact-group.index');
        Route::get('/contact-group/detail', [AccurateController::class, 'index'])->name('spa.accurate-contact-group-detail.index');
        Route::get('/actual-stocks', [AccurateActualStocksController::class, 'index'])->name('accurate.actual-stocks');
        Route::get('/stock-count', [AccurateController::class, 'index'])->name('accurate.stock-count');
        Route::get('/stock-comparison', [AccurateController::class, 'index'])->name('spa.accurate-stock-comparison.index');
        Route::get('/stock-awal-accurate', [AccurateController::class, 'index'])->name('spa.accurate-stock-awal-accurate.index');
        Route::get('/stock-awal-opname', [AccurateController::class, 'index'])->name('spa.accurate-stock-awal-opname.index');
        Route::get('/visit-list', [AccurateController::class, 'index'])->name('spa.accurate-visit-list.index');
    });
});

Route::group(['prefix' => 'webhook'], function () {
    Route::post('/run-artisan', [WebhookController::class, 'runScript'])->name('webhook.run.artisan');
    Route::post('/assign-to-warehouse', [WebhookController::class, 'assignToWarehouse'])->name('webhook.assigntowarehouse');
});

Route::get('/logout', function () {
    return redirect('/login/dashboard');
});


// Route::get('/update-role', function () {
//     $user = TeamUser::whereRole('')->get();

//     foreach ($user as $key => $value) {
//         if ($value->user) {
//             $value->update(['role' => $value->user->role->role_type]);
//         }
//     }

//     return response()->json($user);
// });

// Route::get('/adjust-stock', function () {
//     // qty order
//     $lists = ['jumat pagi'];
//     $product_lists = [
//         [
//             // Flimnoodle Mie Shirataki (Mie Tanpa Goreng) - 1 Box (isi 5 pcs)
//             'id' => 72,
//             'stock' => 100
//         ],
//         [
//             // Flimrice 1 Box (7 Sachet)
//             'id' => 77,
//             'stock' => 100
//         ],
//         [
//             // FLIMBEAUTY Flimcol 5000mg Collagen Tripeptide - 1 box (isi 10)
//             'id' => 75,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber Travel Pack Raspberry (5 sachet)
//             'id' => 64,
//             'stock' => 100
//         ],
//         [
//             // Flimeal  1 Box (isi 12 sachet) Coklat Susu
//             'id' => 58,
//             'stock' => 100
//         ],
//         [
//             // Flimeal 1 sachet Coklat Susu
//             'id' => 60,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber Travel Pack Mango (5 sachet)
//             'id' => 63,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber 1 Box Mango (isi 16 sachet)
//             'id' => 57,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber Mango 1 Sachet
//             'id' => 59,
//             'stock' => 100
//         ],
//         [
//             // Flimeal Travel Pack Coklat Susu (4 sachet)
//             'id' => 65,
//             'stock' => 100
//         ],
//         [
//             // Flimeal Vanilla 1 Box (Isi 12 Sachet)
//             'id' => 67,
//             'stock' => 100
//         ],
//         [
//             // Flimeal Taro 1 box (Isi 12 Sachet)
//             'id' => 68,
//             'stock' => 100
//         ],
//         [
//             // Flimeal Strawberry 1 Box (Isi 12 Sachet)
//             'id' => 69,
//             'stock' => 100
//         ],
//         [
//             // Flimnoodle Mie Shirataki (Mie Tanpa Goreng ) - 1 Sachet
//             'id' => 73,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber 1 Box Blackcurrant (isi 16 sachet)
//             'id' => 18,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber Blackcurrant 1 Sachet
//             'id' => 51,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber Raspberry 1 Box (isi 16 sachet)
//             'id' => 50,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber Raspberry 1 Sachet
//             'id' => 52,
//             'stock' => 100
//         ],
//         [
//             // Flimeal 1 Box isi 12 sachet Coklat Vegan
//             'id' => 20,
//             'stock' => 100
//         ],
//         [
//             // Flimty Fiber Travel Pack Blackcurrant (5 sachet)
//             'id' => 62,
//             'stock' => 100
//         ],
//         [
//             //Flimeal  1 sachet Coklat Vegan
//             'id' => 54,
//             'stock' => 100
//         ],
//         [
//             // Flimty Pink Edition
//             'id' => 70,
//             'stock' => 100
//         ],
//         [
//             // Flimbar 1 sachet
//             'id' => 71,
//             'stock' => 100
//         ],
//         [
//             // Flimburn 1 botol isi 60 tablet
//             'id' => 53,
//             'stock' => 100
//         ],
//         [
//             // Botol Shaker 700ml
//             'id' => 74,
//             'stock' => 100
//         ],
//         [
//             // FLIMBEAUTY Flimcol 5000m - 1 sachet
//             'id' => 76,
//             'stock' => 100
//         ],
//         [
//             // Flimrice 1 Box (7 Sachet)
//             'id' => 77,
//             'stock' => 100
//         ],
//     ];

//     $warehouse_id = 2; // isi warehouse id check db
//     $account_id = 1; // Isi account id PT/NON PT check DB
//     foreach ($product_lists as $key => $item) {
//         $product = Product::find($item['id']);
//         ProductStock::where('product_id', $product->id)->where('company_id', $account_id)->where('warehouse_id', $warehouse_id)->delete();
//         ProductStock::create([
//             'product_id' => $product->id,
//             'warehouse_id' => $warehouse_id, // isi warehouse id check db
//             'is_allocated' => 1, // default 1
//             'company_id' => $account_id, // Isi account id PT/NON PT check DB
//             'stock' => $item['stock']
//         ]);

//         foreach ($product->variants as $key => $variant) {
//             ProductVariantStock::where('product_variant_id', $variant->id)->where('company_id', $account_id)->where('warehouse_id', $warehouse_id)->delete();
//             ProductVariantStock::create([
//                 'product_variant_id' => $variant->id,
//                 'warehouse_id' => $warehouse_id, // isi warehouse id check db
//                 'company_id' => $account_id, // Isi account id PT/NON PT check DB
//                 'qty' => $item['stock'],
//                 'stock_of_market' => floor($item['stock'] / $variant->qty_bundling) ?? 0,
//             ]);
//         }
//     }

//     return response()->json($lists);
// });


// Route::get('/check-stock', function () {
//     $products = Product::with('variants')->get();

//     $lists = [];
//     foreach ($products as $key => $product) {
//         $lists[] = [
//             'product_name' => $product->name,
//             'stock' => $product->final_stock,
//             'stock_warehouse' => $product->stock_warehouse,
//             'variants' => $product->variants()->get()->map(function ($item) {
//                 return [
//                     'variant_name' => $item->name,
//                     'stock' => $item->final_stock,
//                     'stock_warehouse' => $item->stock_warehouse,
//                 ];
//             })
//         ];
//     }

//     return response()->json($lists);
// });

Route::get('/adjust-stock', function () {
    // qty order
    $lists = ['selasa sore'];
    $product_lists = [
        [
            // Flimnoodle Mie Shirataki (Mie Tanpa Goreng) - 1 Box (isi 5 pcs)
            'id' => 72,
            'stock' => 1000
        ],
        [
            // Flimrice 1 Box (7 Sachet)
            'id' => 77,
            'stock' => 1000
        ],
        [
            // Flimrice 1 Pouch (1Kg)
            'id' => 78,
            'stock' => 1000
        ],
        [
            // FLIMBEAUTY Flimcol 5000mg Collagen Tripeptide - 1 box (isi 10)
            'id' => 75,
            'stock' => 1000
        ],
        [
            // Flimty Fiber Travel Pack Raspberry (5 sachet)
            'id' => 64,
            'stock' => 1000
        ],
        [
            // Flimeal  1 Box (isi 12 sachet) Coklat Susu
            'id' => 58,
            'stock' => 1000
        ],
        [
            // Flimeal 1 sachet Coklat Susu
            'id' => 60,
            'stock' => 1000
        ],
        [
            // Flimty Fiber Travel Pack Mango (5 sachet)
            'id' => 63,
            'stock' => 8862
        ],
        [
            // Flimty Fiber 1 Box Mango (isi 16 sachet)
            'id' => 57,
            'stock' => 1000
        ],
        [
            // Flimty Fiber Mango 1 Sachet
            'id' => 59,
            'stock' => 1000
        ],
        [
            // Flimeal Travel Pack Coklat Susu (4 sachet)
            'id' => 65,
            'stock' => 1000
        ],
        [
            // Flimeal Vanilla 1 Box (Isi 12 Sachet)
            'id' => 67,
            'stock' => 1000
        ],
        [
            // Flimeal Taro 1 box (Isi 12 Sachet)
            'id' => 68,
            'stock' => 1000
        ],
        [
            // Flimeal Strawberry 1 Box (Isi 12 Sachet)
            'id' => 69,
            'stock' => 1000
        ],
        [
            // Flimnoodle Mie Shirataki (Mie Tanpa Goreng ) - 1 Sachet
            'id' => 73,
            'stock' => 1000
        ],
        [
            // Flimty Fiber 1 Box Blackcurrant (isi 16 sachet)
            'id' => 18,
            'stock' => 1000
        ],
        [
            // Flimty Fiber Blackcurrant 1 Sachet
            'id' => 51,
            'stock' => 1000
        ],
        [
            // Flimty Fiber Raspberry 1 Box (isi 16 sachet)
            'id' => 50,
            'stock' => 1000
        ],
        [
            // Flimty Fiber Raspberry 1 Sachet
            'id' => 52,
            'stock' => 1000
        ],
        // [
        //     // Flimeal 1 Box isi 12 sachet Coklat Vegan
        //     'id' => 20,
        //     'stock' => 0
        // ],
        [
            // Flimty Fiber Travel Pack Blackcurrant (5 sachet)
            'id' => 62,
            'stock' => 1000
        ],
        [
            //Flimeal  1 sachet Coklat Vegan
            'id' => 54,
            'stock' => 1000
        ],
        // [
        //     // Flimty Pink Edition
        //     'id' => 70,
        //     'stock' => 124
        // ],
        [
            // Flimbar 1 sachet
            'id' => 71,
            'stock' => 1000
        ],
        [
            // Flimburn 1 botol isi 60 tablet
            'id' => 53,
            'stock' => 1000
        ],
        [
            // Botol Shaker 700ml
            'id' => 74,
            'stock' => 1000
        ],
        // [
        //     // FLIMBEAUTY Flimcol 5000m - 1 sachet
        //     'id' => 76,
        //     'stock' => 100
        // ],
    ];

    $warehouse_id = 2; // isi warehouse id check db
    $account_id = 1; // Isi account id PT/NON PT check DB
    foreach ($product_lists as $key => $item) {
        $product = Product::find($item['id']);
        ProductStock::where('product_id', $product->id)->where('company_id', $account_id)->where('warehouse_id', $warehouse_id)->delete();
        ProductStock::create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse_id, // isi warehouse id check db
            'is_allocated' => 1, // default 1
            'company_id' => $account_id, // Isi account id PT/NON PT check DB
            'stock' => $item['stock']
        ]);

        foreach ($product->variants as $key => $variant) {
            ProductVariantStock::where('product_variant_id', $variant->id)->where('company_id', $account_id)->where('warehouse_id', $warehouse_id)->delete();
            ProductVariantStock::create([
                'product_variant_id' => $variant->id,
                'warehouse_id' => $warehouse_id, // isi warehouse id check db
                'company_id' => $account_id, // Isi account id PT/NON PT check DB
                'qty' => $item['stock'],
                'stock_of_market' => floor($item['stock'] / $variant->qty_bundling) ?? 0,
            ]);
        }
    }

    return response()->json($lists);
});

Route::get('/queue-log-tele', function () {
    $message = 'Test Backend';
    $channelId = '-1001993747907';

    try {
        Telegram::sendMessage([
            'chat_id' => $channelId,
            'text' => $message,
        ]);

        return response()->json(['success' => true, 'message' => 'Notification sent successfully']);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Failed to send notification']);
    }

    return response()->json(['okee']);
});
Route::get('/get-shop', function () {
    $app_secret = 'c2e6baff05d29d9ca3faf83e2e46e09aa361b8e6';
    $app_key = '69842a87f535n';
    $timestamp = time();
    $queries = array('app_key' => $app_key, 'timestamp' => $timestamp, 'shop_id' => '');
    $sign = generateSHA256Tiktok('/api/authorization/202309/shops', $queries, $app_secret);

    return response()->json(['sign' => $sign, 'timestamp' => $timestamp]);
});

Route::get('/update-gp-token', function () {
    Artisan::call('gp:token');

    return response()->json(['oke']);
});

Route::get('/update-stock-movement', function () {
    Artisan::call('log:stock-movement');

    return response()->json(['oke']);
});

Route::get('/check-appendix', function () {
    $customer = ModelsUser::find('35984b1a-11f5-4e39-9294-a629c2ee7632', ['id', 'appendix', 'company_id']);

    return response()->json([$customer]);
});

Route::get('/add-customer-number', function () {
    $customers = ModelsUser::whereHas('roles', function ($query) {
        return $query->where('role_type', 'member');
    })->where('uid', 'iHRYt')->get();

    foreach ($customers as $customer) {
        $customer->update(['uid' => Str::random(5)]);
    }

    return response()->json(['okee']);
});

Route::get('/update-check', function () {
    $msg = "update 8 Sept 16:00";
    return response()->json($msg);
});

Route::get('/test-queue', function () {
    TestQueue::dispatch()->onQueue('queue-log');
    return response()->json(['okee']);
});

Route::get('/cek-stock-movement', function () {
    $results = DB::table('order_deliveries as od')
        ->select(DB::raw('SUM(tbl_od.qty_delivered * tbl_pv.qty_bundling) as qty_delivery'))
        ->join('product_needs as pn', 'od.product_need_id', '=', 'pn.id')
        ->join('product_variants as pv', 'pn.product_id', '=', 'pv.id')
        ->where('od.status', '!=', 'cancel')
        ->whereDate('od.created_at', Carbon::now())
        ->get();
    return response()->json($results);
});

Route::get('/update-price-konsinyasi', function () {
    $orders = DB::table('order_transfers')->where('total', 0)->orderBy('created_at', 'DESC')->get();
    foreach ($orders as $key => $order) {
        UpdatePriceQueue::dispatch($order, 'order_transfers')->onQueue('queue-backend');
    }
    return response()->json(['okee' => Carbon::now()]);
});

Route::get('/get-konsinyasi', function () {
    $bins = DB::table('master_bins as a')
        ->join('master_bin_stocks as b', 'a.id', '=', 'b.master_bin_id')
        ->join('products as c', 'b.product_id', '=', 'c.id')
        // ->join('master_bin_users as d', 'a.id', '=', 'd.master_bin_id')
        // ->join('users as e', 'd.user_id', '=', 'e.id')
        ->select(
            'a.id as master_bin_id',
            'b.product_id',
            // 'e.uid',
            'c.name as product_name',
            'a.name as bin_name',
            DB::raw('SUM(tbl_b.stock) as qty'),
            // DB::raw('GROUP_CONCAT(DISTINCT tbl_e.name) as user_names')
        )
        ->groupBy('a.id', 'b.product_id')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->master_bin_id,
                'product_id' => $item->product_id,
                // 'uid' => $item->uid,
                'product_name' => $item->product_name,
                'bin_name' => $item->bin_name,
                'qty' => $item->qty,
                // 'user_name' => explode(',', $item->user_names)
            ];
        });

    return response()->json(['data' => $bins]);
});

Route::get('/import-npwp', function () {
    $data = [
        [
            "uid" => "ALP2053",
            "npwp" => "536952625451000",
            "nama_npwp" => "PT PRIMA RETAIL INDONESIA",
            "alamat" =>
            "JALAN GADING GOLF BOULEVARD RUKO BERRYL 2 NOMO PAKULONAN BARAT KELAPA DUA KAB. TANGERANG BANTEN",
        ]
    ];

    $bins = [];
    $error = [];
    $success = [];
    foreach ($data as $key => $bin) {
        try {
            DB::beginTransaction();
            $user = DB::table('users')->where('uid', $bin['uid'])->select('id', 'name')->first();
            if ($user) {
                $bins[] = ['id' => $user->id, 'name' => $user->name];
                DB::table('companies')->where('user_id', $user->id)->updateOrInsert(['user_id' =>  $user->id], [
                    'npwp' => $bin['npwp'],
                    'npwp_name' => $bin['nama_npwp'],
                    'address' => $bin['alamat'],
                    'user_id' =>  $user->id,
                    'need_faktur' => 1
                ]);
                $success[] = [
                    'type' => 'success',
                    'uid' => $bin['uid']
                ];
            } else {
                $error[] = [
                    'type' => 'error',
                    'uid' => $bin['uid'],
                    'message' => 'Contact tidak terdaftar',
                ];
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $error[] = [
                'type' => 'error',
                'uid' => $bin['uid'],
                'message' =>  $th->getMessage(),
            ];
        }
    }
    return response()->json(['error' => $error, 'success' => $success]);
});

Route::get('/map-contact', function () {
    // Format data baru
    $CustomerCodes = [
        ["group_code" => "B1305", "uid_contact" => "ALP2053"],
        ["group_code" => "B1305", "uid_contact" => "ALP2054"],
        ["group_code" => "B1305", "uid_contact" => "ALP2055"],
        ["group_code" => "B1305", "uid_contact" => "ALP2056"],
        ["group_code" => "B1305", "uid_contact" => "ALP2057"],
        ["group_code" => "B1305", "uid_contact" => "ALP2059"],
        ["group_code" => "B1305", "uid_contact" => "ALP2060"],
        ["group_code" => "B1305", "uid_contact" => "ALP2061"],
        ["group_code" => "B1305", "uid_contact" => "ALP2062"],
        ["group_code" => "B1305", "uid_contact" => "ALP2063"],
        ["group_code" => "B1305", "uid_contact" => "ALP2064"],
        ["group_code" => "B1305", "uid_contact" => "ALP2065"],
        ["group_code" => "B1305", "uid_contact" => "ALP2066"],
        ["group_code" => "B1305", "uid_contact" => "ALP2067"],
        ["group_code" => "B1305", "uid_contact" => "ALP2068"],
        ["group_code" => "B1305", "uid_contact" => "ALP2070"],
        ["group_code" => "B1305", "uid_contact" => "ALP2071"],
        ["group_code" => "B1305", "uid_contact" => "ALP2072"],
        ["group_code" => "B1305", "uid_contact" => "ALP2073"],
        ["group_code" => "B1305", "uid_contact" => "ALP2074"],
        ["group_code" => "B1305", "uid_contact" => "ALP2075"],
        ["group_code" => "B1305", "uid_contact" => "ALP2076"],
        ["group_code" => "B1305", "uid_contact" => "ALP2077"],
        ["group_code" => "B1305", "uid_contact" => "ALP2078"],
        ["group_code" => "B1305", "uid_contact" => "ALP2079"],
        ["group_code" => "B1305", "uid_contact" => "ALP2080"],
        ["group_code" => "B1305", "uid_contact" => "ALP2081"],
        ["group_code" => "B1305", "uid_contact" => "ALP2083"],
    ];

    $items = [];

    // Mengelompokkan data berdasarkan uid_contact
    foreach ($CustomerCodes as $code) {
        $items[$code['group_code']][] = $code['uid_contact'];
    }

    foreach ($items as $uid_contact => $group_codes) {
        // Buat atau update group berdasarkan group_code
        $group = ContactGroup::updateOrCreate(['code' => $uid_contact], [
            'code' => $uid_contact,
            'name' => 'Group ' . $uid_contact,
            'deskripsi' => 'Import By IT',
            'created_by' => '963b12db-5dbf-4cd5-91f7-366b2123ccb9',
            'updated_by' => '963b12db-5dbf-4cd5-91f7-366b2123ccb9',
        ]);

        foreach ($group_codes as $group_code) {
            // Cari user berdasarkan uid_contact
            $user = DB::table('users')->where('uid', $group_code)->select('id')->first();
            if ($user) {
                // Masukkan data ke contact_group_members
                DB::table('contact_group_members')->updateOrInsert([
                    'contact_group_id' => $group->id,
                    'contact_id' => $user->id,
                    'is_admin' => 0
                ]);
            }
        }
    }

    return response()->json([$items]);
});

Route::get('/get-sign', function () {
    $path = "/api/v2/shop/auth_partner";
    $redirectUrl = "https://www.flimty.co";
    $partnerId = "2005648";
    $partnerKey = "794d79426a4f706c51744e7a7258587a6553744666667a4c4a7871696867484f";
    $host = "https://partner.shopeemobile.com";
    $timest = time();
    $baseString = sprintf("%s%s%s", $partnerId, $path, $timest);
    $sign = hash_hmac('sha256', $baseString, $partnerKey);
    $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s&redirect=%s", $host, $path, $partnerId, $timest, $sign, $redirectUrl);
    return response()->json([$url]);
});

Route::get('/get-token', function () {
    $partnerId = 2005648;
    $partnerKey = "794d79426a4f706c51744e7a7258587a6553744666667a4c4a7871696867484f";
    $host = "https://partner.shopeemobile.com";
    $shopId = 283260334;
    $code = "54654c444874795a5a69757270684155";
    $path = "/api/v2/auth/token/get";

    $timest = time();
    $body = array("code" => $code,  "shop_id" => $shopId, "partner_id" => $partnerId);
    $baseString = sprintf("%s%s%s", $partnerId, $path, $timest);
    $sign = hash_hmac('sha256', $baseString, $partnerKey);
    $url = sprintf("%s%s?partner_id=%s&timestamp=%s&sign=%s", $host, $path, $partnerId, $timest, $sign);


    $c = curl_init($url);
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($c, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    $resp = curl_exec($c);
    echo "raw result: $resp";

    $ret = json_decode($resp, true);
    // $accessToken = $ret["access_token"];
    // $newRefreshToken = $ret["refresh_token"];
    // {"refresh_token":"534b79545455474f646a54726778744a","access_token":"4156546b7578767455676f4f616f4657","expire_in":14376,"request_id":"8be55217a4bd7349859361066f393721","error":"","message":""}
    return response()->json($ret);
});

Route::get('/get-sign-order', function () {
    $credentials = [
        'partnerId' => '2005648',
        'accessToken' => '4156546b7578767455676f4f616f4657',
        'shopId' => '283260334',
        'partnerKey' => '794d79426a4f706c51744e7a7258587a6553744666667a4c4a7871696867484f'
    ];

    // Generate signature only
    $signatureParams = [
        'partnerId' => $credentials['partnerId'],
        'apiPath' => '/api/v2/order/get_order_detail',
        'timestamp' => time(),
        'accessToken' => $credentials['accessToken'],
        'shopId' => $credentials['shopId'],
        'partnerKey' => $credentials['partnerKey']
    ];

    // $signature = generateShopeeSignature($signatureParams);
    // return response()->json(['signature' => $signature, 'params' => $signatureParams]);
    // echo "Generated Signature: " . $signature . "\n";
    // Get order detail
    $orderId = "241221DF1VJ1FF";
    $orderDetail = getOrderDetail($orderId, $credentials);
    return response()->json($orderDetail);
});

Route::get('/cek-export-contact', function () {
    $query = DB::table('users as u')
        ->select([
            'u.id',
            'u.name',
            'u.email',
            'u.telepon',
            'u.gender',
            'u.bod',
            'u.uid',
            'r.role_name',
            'au.alamat',
            'au.catatan',
            'au.kodepos as address_kodepos',
            'prov.nama as provinsi_nama',
            'kab.nama as kabupaten_nama',
            'kec.nama as kecamatan_nama',
            'kel.nama as kelurahan_nama',
            'kel.zip as kelurahan_zip'
        ])
        ->leftJoin('role_user as ur', 'u.id', '=', 'ur.user_id')
        ->leftJoin('roles as r', 'ur.role_id', '=', 'r.id')
        ->leftJoin('address_users as au', 'u.id', '=', 'au.user_id')
        ->leftJoin('addr_provinsi as prov', 'au.provinsi_id', '=', 'prov.pid')
        ->leftJoin('addr_kabupaten as kab', 'au.kabupaten_id', '=', 'kab.pid')
        ->leftJoin('addr_kecamatan as kec', 'au.kecamatan_id', '=', 'kec.pid')
        ->leftJoin('addr_kelurahan as kel', 'au.kelurahan_id', '=', 'kel.pid');
    return response()->json(['data' => $query->get()->toArray(), 'count' => $query->count()]);
});

Route::get('/phpinfo', function () {
    phpinfo();
});

Route::get('/check-time', function () {
    return [
        'app_timezone' => config('app.timezone'),
        'server_time' => now()->toDateTimeString(),
        'php_timezone' => date_default_timezone_get(),
        'current_php_time' => date('Y-m-d H:i:s'),
    ];
});

Route::get('/test-upload', function () {
    return view('test-upload');
});

Route::post('/test-upload', function (Request $request) {
    $request->validate([
        'image' => 'required|image|max:2048', // Batas ukuran 2MB
    ]);

    $file = $request->file('image');
    $filename = 'uploads/' . time() . '-' . $file->getClientOriginalName();

    try {
        // Upload file ke Backblaze B2 dengan nama asli
        Storage::disk('s3')->put($filename, file_get_contents($file), 'public');

        return response()->json([
            'message' => 'Upload berhasil!',
            'path' => $filename,
            'url' => Storage::disk('s3')->exists($filename) ? Storage::disk('s3')->temporaryUrl($filename, now()->addMinutes(60)) : null, // URL akses file
        ]);
    } catch (\Exception $e) {
        Log::error("Upload gagal ke S3: " . $e->getMessage());
        return response()->json([
            'message' => 'Gagal upload!',
            'error' => $e->getMessage(),
        ], 500);
    }
});

Route::get('/cek-accurate', function () {

    $timestamp = '1749779487385';
    $secret = 'be7oZxpiPDXooS4ra2Hut3aLhB74lUi9yblxC2DKGPO2Mt7DhqhGttpKj57rnWnY';

    $signature = hash_hmac('sha256', utf8_encode($timestamp), utf8_encode($secret));
    // echo $signature;
    // $msg = "update 24 mar 11:01";
    return response()->json($signature);
});

Route::get('/test-email', function (Request $request) {
    $email = $request->query('email');

    if (!$email) {
        return '❌ Parameter "email" wajib diisi. Contoh: /test-email?email=fikar.bidflow@gmail.com';
    }

    try {
        Mail::raw('Ini adalah email test dari Sistem', function ($message) use ($email) {
            $message->to($email)
                ->subject('Test Email');
        });

        return "✅ Email berhasil dikirim ke {$email}";
    } catch (\Exception $e) {
        return '❌ Gagal mengirim email: ' . $e->getMessage();
    }
});
