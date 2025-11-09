<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\BusinessEntity;
use App\Models\Cases;
use App\Models\Category;
use App\Models\CategoryCase;
use App\Models\CompanyAccount;
use App\Models\GpBatchId;
use App\Models\GpSiteId;
use App\Models\Kecamatan;
use App\Models\Logistic;
use App\Models\MasterBin;
use App\Models\MasterDiscount;
use App\Models\MasterTax;
use App\Models\Package;
use App\Models\PaymentTerm;
use App\Models\PriorityCase;
use App\Models\Product;
use App\Models\ProductAdditional;
use App\Models\ProductStock;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\RefundMaster;
use App\Models\ReturMaster;
use App\Models\Role;
use App\Models\SkuMaster;
use App\Models\SourceCase;
use App\Models\StatusCase;
use App\Models\TypeCase;
use App\Models\User;
use App\Models\Variant;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Models\OrderTransfer;
use App\Models\InventoryDetailItem;
use App\Models\MasterBinUser;
use App\Models\PaymentMethod;
use App\Models\ProductCarton;
use App\Models\ProductVariantBundlingStock;
use App\Models\PurchaseRequitition;
use App\Models\PurchaseRequititionItem;
use App\Models\PurchaseRequititionApproval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterController extends Controller
{
    public function getProductCartonById($id)
    {
        $carton = ProductCarton::find($id);
        return response()->json([
            'status' => 'success',
            'data' => $carton
        ]);
    }

    public function getProductMasterById($id)
    {
        $product = Product::leftjoin('product_cartons', 'products.product_carton_id', 'product_cartons.id')->select('product_cartons.*')->where('products.id', $id)->first();
        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    public function getBrand()
    {
        $brand = Brand::where('status', 1)->get();
        return response()->json([
            'status' => 'success',
            'data' => $brand
        ]);
    }

    public function getBussinnesEntity()
    {
        $bussinesEntity = BusinessEntity::all();
        return response()->json([
            'status' => 'success',
            'data' => $bussinesEntity
        ]);
    }

    public function getRole($role_user = 'superadmin')
    {
        if (in_array($role_user, ['superadmin', 'admin', 'adminsales', 'leadwh'])) {
            $role = Role::all();
            return response()->json([
                'status' => 'success',
                'data' => $role
            ]);
        }

        $role = Role::whereIn('role_type', ['member', 'agent', 'subagent'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $role
        ]);
    }

    public function getRoleRequisition()
    {
        $role = Role::all();
        return response()->json([
            'status' => 'success',
            'data' => $role
        ]);
    }

    public function getSku()
    {
        $sku  = SkuMaster::all();
        return response()->json([
            'status' => 'success',
            'data' => $sku
        ]);
    }

    public function getProductCarton()
    {
        $carton  = ProductCarton::all();
        return response()->json([
            'status' => 'success',
            'data' => $carton
        ]);
    }

    public function getProvinsi()
    {
        $provinsi = DB::table('addr_provinsi')->get();
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Provinsi Lists',
            'data' => $provinsi
        ];
        return response()->json($respon, 200);
    }

    public function getKota($id)
    {
        $kota = DB::table('addr_kabupaten')->where('prov_id', $id)->get();
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Kota Lists',
            'data' => $kota
        ];
        return response()->json($respon, 200);
    }


    public function getKecamatan($id)
    {
        $kecamatan = DB::table('addr_kecamatan')->where('kab_id', $id)->get();
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Kecamatan Lists',
            'data' => $kecamatan
        ];
        return response()->json($respon, 200);
    }


    public function getKelurahan($id)
    {
        $kelurahan = DB::table('addr_kelurahan')->where('kec_id', $id)->get();
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Kelurahan Lists',
            'data' => $kelurahan
        ];
        return response()->json($respon, 200);
    }

    public function getWhid()
    {
        $site = GpSiteId::all();
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'WH ID Lists',
            'data' => $site
        ];
        return response()->json($respon, 200);
    }

    public function getBatchId()
    {
        $batch = GpBatchId::all();
        $respon = [
            'error' => false,
            'status_code' => 200,
            'message' => 'Batch ID Lists',
            'data' => $batch
        ];
        return response()->json($respon, 200);
    }

    public function getWarehouse()
    {
        $warehouses = Warehouse::where('status', 1)->get();

        return response()->json([
            'status' => 'success',
            'data' => $warehouses
        ]);
    }

    public function getMasterBin()
    {
        $warehouses = MasterBin::with('users')->where('status', 1)->get();

        return response()->json([
            'status' => 'success',
            'data' => $warehouses
        ]);
    }

    public function getTop()
    {
        $top = PaymentTerm::all();

        return response()->json([
            'status' => 'success',
            'data' => $top
        ]);
    }

    public function getSoKonsinyasi(Request $request)
    {
        $company_id = $request->account_id;
        $order = OrderTransfer::where('company_id', $company_id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    function getApprovedPurchaseRequests()
    {
        $approvedPrIds = PurchaseRequititionApproval::select('purchase_requitition_id')
            ->groupBy('purchase_requitition_id')
            ->havingRaw('COUNT(*) = SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END)')
            ->pluck('purchase_requitition_id');

        $data = PurchaseRequitition::whereIn('id', $approvedPrIds)
            ->where('request_status', 2)
            ->where('is_po_created', 0)
            ->get(['id', 'pr_number']);

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function getDetailKonsinyasi($id)
    {
        $order = OrderTransfer::find($id);
        $order['product'] = InventoryDetailItem::where('uid_inventory', $order->uid_inventory)->get();

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    public function getDetailPr($id)
    {
        $pr = PurchaseRequitition::where('pr_number', $id)->first();
        $pr['product'] = PurchaseRequititionItem::where('purchase_requitition_id', $pr->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $pr
        ]);
    }

    public function getBinByContact($id)
    {
        $bin = MasterBinUser::with('masterBin')->where('user_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $bin
        ]);
    }

    public function getContactBin($id)
    {
        $bin = MasterBinUser::with('user')->where('master_bin_id', $id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $bin
        ]);
    }

    public function getProductList($sales_channel = null)
    {
        $product = ProductVariant::whereStatus(1)->whereNull('deleted_at');
        if ($sales_channel) {
            $product->where('sales_channel', 'like', "%$sales_channel%");
        }

        $products = $product->get();

        // Add a custom field to each product
        $products->transform(function ($item) use ($sales_channel) {
            if ($sales_channel == 'telmark') {
                $price = $item->prices()->where('level_id', 9)->where('product_variant_id', $item->id)->first();

                if ($price) {
                    $item->price_data = [
                        'basic_price' => $price->basic_price,
                        'final_price' => $price->final_price,
                    ];
                } else {
                    $item->price_data = [
                        'basic_price' => 0,
                        'final_price' => 0,
                    ];
                }
            }

            if ($sales_channel == 'agent-portal') {
                $price = $item->prices()->where('level_id', 5)->where('product_variant_id', $item->id)->first();

                if ($price) {
                    $item->price_data = [
                        'basic_price' => $price->basic_price,
                        'final_price' => $price->final_price,
                    ];
                } else {
                    $item->price_data = [
                        'basic_price' => 0,
                        'final_price' => 0,
                    ];
                }
            }
            return $item;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function getProductListMaster()
    {
        $products = Product::whereStatus(1)->whereNull('deleted_at')->get();
        $account_id = auth()?->user()?->company_id ?? 1;
        $wh_ids = Warehouse::pluck('id')->toArray();
        return response()->json([
            'status' => 'success',
            'data' => tap($products)->map(function ($product) use ($wh_ids, $account_id) {
                $stock_warehouse = ProductStock::where('product_id', $product->id)->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id)->sum('stock');
                // $stock_bundling_warehouse = ProductVariantBundlingStock::whereHas('bundling', function ($query) use ($wh_ids, $product) {
                //     $query->where('product_id', $product->id);
                // })->whereIn('warehouse_id', $wh_ids)->where('company_id', $account_id)->orderBy('qty', 'asc')->first(['qty']);
                $product->stock_by_warehouse = $stock_warehouse > 0 ? $stock_warehouse : 0;
                // $product->stock_bundling_warehouse = $stock_bundling_warehouse ? $stock_bundling_warehouse->qty : 0;

                return $product;
            }),
        ]);
    }

    public function getProductListVariant()
    {
        $product = ProductVariant::whereStatus(1)->whereNull('deleted_at')->get();

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    public function getProductAdditionalList($type)
    {
        $product = ProductAdditional::whereStatus(1)->whereType($type)->get();

        return response()->json([
            'status' => 'success',
            'data' => $product
        ]);
    }

    public function getMasterTax()
    {
        $tax = MasterTax::all();

        return response()->json([
            'status' => 'success',
            'data' => $tax
        ]);
    }

    public function getVendor()
    {
        $vendor = Vendor::all();

        return response()->json([
            'status' => 'success',
            'data' => $vendor
        ]);
    }

    public function getMasterDiscount($sales_channel = null)
    {
        $tax = MasterDiscount::all();
        if ($sales_channel) {
            $tax = MasterDiscount::where('sales_channel', 'like', "%$sales_channel%")->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $tax
        ]);
    }

    public function getPackage()
    {
        $package = Package::all();

        return response()->json([
            'status' => 'success',
            'data' => $package
        ]);
    }

    public function getVariant()
    {
        $package = Variant::all();

        return response()->json([
            'status' => 'success',
            'data' => $package
        ]);
    }


    public function getTypeCase()
    {
        $typeCase = TypeCase::all();

        return response()->json([
            'status' => 'success',
            'data' => $typeCase
        ]);
    }

    public function getCategory()
    {
        $category = Category::all();

        return response()->json([
            'status' => 'success',
            'data' => $category
        ]);
    }

    public function getOfflineExpedition()
    {
        $logistic = Logistic::where('logistic_type', 'offline')->get();
        return response()->json([
            'status' => 'success',
            'data' => $logistic
        ]);
    }

    // get company account
    public function getCompanyAccount()
    {
        $companyAccount = CompanyAccount::all();
        return response()->json([
            'status' => 'success',
            'data' => $companyAccount
        ]);
    }

    //  get product stock
    public function getProductStockMaster(Request $request)
    {
        $productStock = ProductStock::where('product_id', $request->product_id)->where('warehouse_id', $request->warehouse_id)->groupBy('product_id')->select('*')->selectRaw("SUM(stock) as stock_total")->orderBy('is_allocated', 'asc')->get();
        $stock = [];

        foreach ($productStock as $key => $item) {
            $stock[] = [
                'key' => $key,
                'id' => $item->id,
                'product_id' => $item->product_id,
                'qty' => intval($item->stock_total),
                'from_warehouse_id' => $request->warehouse_id,
                'to_warehouse_id' => null,
                'sku' => $item->product?->sku,
                'u_of_m' => $item->product?->u_of_m,
                'is_allocated' => false,
                'qty_alocation' => 0,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $stock
        ]);
    }

    // get vendor
    public function getVendors()
    {
        $vendor = Vendor::select('vendor_code', 'name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $vendor->map(function ($item) {
                return [
                    'code' => $item->vendor_code,
                    'name' => $item->name
                ];
            })
        ]);
    }

    // get case list
    public function getCaseList()
    {
        $cases = [];

        // manual case
        $manual_cases = Cases::all();
        foreach ($manual_cases as $manual_case) {
            $cases[] = [
                'id' => $manual_case->id,
                'name' => $manual_case->title,
                'type' => 'manual'
            ];
        }

        // refund case
        $refund_cases = RefundMaster::all();
        foreach ($refund_cases as $refund_case) {
            $cases[] = [
                'id' => $refund_case->id,
                'name' => $refund_case->title,
                'type' => 'refund'
            ];
        }

        // return case
        $return_cases = ReturMaster::all();
        foreach ($return_cases as $return_case) {
            $cases[] = [
                'id' => $return_case->id,
                'name' => $return_case->title,
                'type' => 'return'
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $cases
        ]);
    }

    public function getProductByCase(Request $request)
    {
        $case = null;
        $type_case = $request->case_type;
        $case_title = $request->case_title;
        if ($type_case == 'manual') {
            $case = Cases::where('title', $case_title)->first();
        } else if ($type_case == 'refund') {
            $case = RefundMaster::where('title', $case_title)->first();
        } else if ($type_case == 'return') {
            $case = ReturMaster::where('title', $case_title)->first();
        }

        return response()->json([
            'status' => 'success',
            'data' => $case->items
        ]);
    }

    public function getSourceCase()
    {
        $type = SourceCase::all();
        return response()->json([
            'status' => 'success',
            'data' => $type
        ]);
    }

    public function getPriorityCase()
    {
        $type = PriorityCase::all();
        return response()->json([
            'status' => 'success',
            'data' => $type
        ]);
    }

    public function getStatusCase()
    {
        $type = StatusCase::all();
        return response()->json([
            'status' => 'success',
            'data' => $type
        ]);
    }

    public function getCategoryCase()
    {
        $type = CategoryCase::all();
        return response()->json([
            'status' => 'success',
            'data' => $type
        ]);
    }

    public function getLogistic()
    {
        $type = Logistic::all();
        return response()->json([
            'status' => 'success',
            'data' => $type
        ]);
    }

    // seacrh province
    public function searchAddress(Request $request)
    {
        $kelurahan = DB::table('addr_kelurahan as kel')
            ->leftJoin('addr_kecamatan as kec', 'kec.pid', '=', 'kel.kec_id')
            ->leftJoin('addr_kabupaten as kab', 'kab.pid', '=', 'kec.kab_id')
            ->leftJoin('addr_provinsi as pro', 'pro.pid', '=', 'kab.prov_id')
            ->where('kec.nama', 'like', "%$request->search%")
            ->select('kec.pid as kec_id', 'kel.pid as kel_id', 'kab.pid as kab_id', 'pro.pid as prov_id', 'kec.nama as kec_nama', 'kel.nama as kel_nama', 'kab.nama as kab_nama', 'pro.nama as prov_nama', 'kel.zip as kodepos')->orderBy('kel.nama', 'ASC')->orderBy('kel.zip', 'DESC')->get();

        $results = [];
        foreach ($kelurahan as $item) {
            $results[] = [
                'value' => $item->kel_id,
                'kec_id' => $item->kec_id,
                'kel_id' => $item->kel_id,
                'kab_id' => $item->kab_id,
                'prov_id' => $item->prov_id,
                'label' => $item->kel_nama . '/' . $item->kec_nama . '/' . $item->kab_nama . '/' . $item->prov_nama . ' - ' . $item->kodepos,
                'kodepos' => $item->kodepos,
            ];
        }

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }

    // load user by phone
    public function loadUserByPhone(Request $request)
    {
        $user = User::where('telepon', formatPhone($request->phone))->first();
        // $address = [];

        // foreach ($user->addressUsers as $address) {
        //     $address[] = [
        //         // 'label' => $address->kec_id,
        //         'value' => $address->kecamatan,
        //     ];
        // }
        if ($user) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'telepon' => $user->telepon,
                    'email' => $user->email,
                    'address' => $user->addressUsers
                ]
            ]);
        }
        return response()->json([
            'status' => 'success',
            'data' => null
        ]);
    }

    public function getPaymentTerm()
    {
        $paymentTerms = PaymentTerm::all();

        return response()->json([
            'status' => 'success',
            'data' => $paymentTerms
        ]);
    }

    public function getPaymentMethod()
    {
        $results = PaymentMethod::with('children')->whereNull('parent_id')->whereStatus(1)->get();

        return response()->json([
            'status' => 'success',
            'data' => $results
        ]);
    }
}
