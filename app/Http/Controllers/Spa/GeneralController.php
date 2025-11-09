<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\AddressUser;
use App\Models\Company;
use App\Models\CompanyAccount;
use App\Models\CompanyUser;
use App\Models\GpCheckbook;
use App\Models\LeadMaster;
use App\Models\MasterTax;
use App\Models\Menu;
use App\Models\Notification;
use App\Models\OrderDelivery;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\ProductAdditional;
use App\Models\ProductNeed;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\InventoryDetailItem;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pusher\Pusher;

class GeneralController extends Controller
{
    public function loadUser()
    {
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'data' => $user
        ]);
    }

    public function loadUserMenu()
    {
        $user = auth()->user();
        $role = Role::with('menus')->find($user->role->id);
        $role_id = $role->id;
        $menus = $role->menus()->where('show_menu', 1)->with(['children' => function ($query)  use ($role_id) {
            $query->whereHas('roles', function ($subquery) use ($role_id) {
                return $subquery->where('role_id', $role_id);
            });
        }])->whereHas('roles', function ($query) use ($role_id) {
            return $query->where('role_id', $role_id);
        })->where('parent_id')->orderBy('menu_order', 'ASC')->get();

        $menu_data =  $menus?->map(function ($menu) {
            if (!in_array($menu['menu_route'], ['#', null, ''])) {
                $menu['spa_route'] = null;
                try {
                    $route = route($menu['menu_route']);
                    $menu['menu_url'] = $route;
                    if (strpos($menu['menu_route'], 'spa') !== false) {
                        $menu['spa_route'] = str_replace(env('APP_URL'), '', $route);
                    }
                } catch (\Throwable $th) {
                    $menu['menu_url'] = '#';
                    $menu['spa_route'] = null;
                }
                if ($menu['badge']) {
                    $menu['badge_count'] = getBadge($menu['badge']);
                }
            }

            foreach ($menu['children'] as $key => $children) {
                $menu['children'][$key]['menu_url'] = '#';
                $menu['children'][$key]['spa_route'] = null;
                if ($children['menu_route']) {
                    try {
                        $route = route($children['menu_route']);
                        $menu['children'][$key]['menu_url'] = $route;
                        if (strpos($children['menu_route'], 'spa') !== false) {
                            $menu['children'][$key]['spa_route'] = str_replace(env('APP_URL'), '', $route);
                        }
                    } catch (\Throwable $th) {
                        $menu['children'][$key]['menu_url'] = '#';
                        $menu['children'][$key]['spa_route'] = false;
                    }
                    if ($children['badge']) {
                        $menu['children'][$key]['badge_count'] = getBadge($children['badge']);
                    }
                }
            }

            return $menu;
        });

        return response()->json([
            'status' => 'success',
            'data' => $menu_data
        ]);
    }

    public function storeSetting(Request $request)
    {
        setSetting($request->key, $request->value);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function loadSetting(Request $request)
    {
        $value = getSetting($request->key);

        return response()->json([
            'status' => 'success',
            'data' => $value
        ]);
    }

    public function deleteSetting(Request $request)
    {
        removeSetting($request->key);

        return response()->json([
            'status' => 'success',
        ]);
    }

    public function getContact(Request $request)
    {
        $user = auth()->user();
        $role = $user->role->role_type;

        $user_list = User::query();

        if (!in_array($role, ['superadmin', 'adminsales', 'leadwh', 'leadsales', 'admin', 'finance'])) {
            $user_list->where('created_by', $user->id);
        }

        if ($request->search) {
            $user_list->where('name', 'like', '%' . $request->search . '%');
        }

        if (in_array($role, ['sales', 'leadcs'])) {
            $user_list->whereHas('roles', function ($query) {
                $query->whereIn('role_type', ['agent', 'member', 'subagent']);
            });
        }

        $userData = $user_list->limit($request->limit ?? 5)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name . ' - ' . $item->role?->role_name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $userData,
        ]);
    }

    public function getContactOwner(Request $request)
    {
        $user = auth()->user();
        $role = $user->role->role_type;

        $user_list = User::query()->where('sales_channel', 'karyawan');


        if ($request->search) {
            $user_list->where('name', 'like', '%' . $request->search . '%');
        }

        $userData = $user_list->limit($request->limit ?? 5)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name . ' - ' . $item->role?->role_name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $userData,
        ]);
    }

    public function getContactWarehouse(Request $request)
    {
        $user_list = User::query();

        if ($request->search) {
            $user_list->where('name', 'like', '%' . $request->search . '%');
        }

        $user_list->whereHas('roles', function ($query) {
            $query->whereIn('role_type', ['warehouse', 'admindelivery']);
        });

        $userData = $user_list->limit($request->limit ?? 5)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name . ' - ' . $item->role?->role_name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $userData,
        ]);
    }
    public function getContactAhliGizi(Request $request)
    {
        $user_list = User::query();

        if ($request->search) {
            $user_list->where('name', 'like', '%' . $request->search . '%');
        }

        $user_list->whereHas('roles', function ($query) {
            $query->whereIn('role_type', ['ahligizi']);
        });

        $userData = $user_list->limit($request->limit ?? 5)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name . ' - ' . $item->role?->role_name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $userData,
        ]);
    }

    public function getSales(Request $request)
    {
        $user = auth()->user();
        $user_list = User::query();
        if ($request->search) {
            $user_list->where('name', 'like', '%' . $request->search . '%');
        }
        $user_list->whereHas('roles', function ($query) {
            $query->whereIn('role_type', ['sales', 'leadsales', 'adminsales', 'leadwh']);
        });

        $userData = $user_list->whereNotIn('id', [$user->id])->limit($request->limit ?? 5)->get()->map(function ($item) use ($user) {

            return [
                'id' => $item->id,
                'nama' => $item->name
            ];
        });

        $newUser = [];

        foreach ($userData as $key => $value) {
            $newUser[0]['id'] = $user->id;
            $newUser[0]['nama'] = $user->name;
            $newUser[$key + 1] = $value;
        }

        return response()->json([
            'status' => 'success',
            'data' => $newUser
        ]);
    }

    public function getWarehouseUser(Request $request)
    {
        $warehouse = User::query();

        if ($request->search) {
            $warehouse->where('name', 'like', '%' . $request->search . '%');
        }

        $warehouses = $warehouse->whereHas('roles', function ($query) {
            $query->whereIn('role_type', ['warehouse', 'admindelivery']);
        })->get();

        return response()->json([
            'status' => 'success',
            'data' => $warehouses
        ]);
    }

    public function getCompany(Request $request)
    {
        $company = Company::query();

        if ($request->search) {
            $company->where('name', 'like', '%' . $request->search . '%');
        }

        $companies = $company->limit($request->limit ?? 5)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $companies
        ]);
    }

    public function getAddressUser($user_id)
    {
        if ($user_id) {
            $address = AddressUser::where('user_id', $user_id)->get();

            return response()->json([
                'status' => 'success',
                'data' => $address
            ]);
        }
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function getAddressWithUser($user_id)
    {
        if ($user_id) {
            $user = User::with('addressUsers')->find($user_id);
            if ($user) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->telepon,
                        'address' => $user->addressUsers
                    ]
                ]);
            }
        }
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }



    public function updateProductNeed(Request $request)
    {
        $productNeed =  ProductNeed::find($request->item_id);
        ProductNeed::whereUidLead($productNeed->uid_lead)->update([
            $request->field => $request->value,
        ]);

        $order = OrderManual::whereUidLead($productNeed->uid_lead)->first();
        if ($request->field == 'discount') {
            $subtotal = $order->subtotal;
            $discount_amount = $request->value * $productNeed->qty;
            $tax_percentage = (float) $order->tax_percentage;
            $dpp = $subtotal - $discount_amount;
            $ppn = $dpp * $tax_percentage;
            $order->update([
                'diskon' => $discount_amount,
                'dpp' => $dpp,
                'ppn' => $ppn,
                'total' => $dpp + $ppn + $order->kode_unik + $order->ongkir,
            ]);
        }
        if ($request->field == 'tax_id') {
            $tax = MasterTax::find($request->value);
            $subtotal = $order->subtotal;
            $discount_amount = $order->diskon;
            $tax_percentage = (float)  $tax->tax_percentage > 0 ? $tax->tax_percentage / 100 : 0;
            $dpp = $subtotal - $discount_amount;
            $ppn = $dpp * $tax_percentage;
            $order->update([
                'dpp' => $dpp,
                'tax_percentage' => $tax_percentage,
                'ppn' => $ppn,
                'total' => $dpp + $ppn + $order->kode_unik + $order->ongkir,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => $request->field . ' Produk berhasil diubah!',
        ]);
    }

    public function updateOrderNotes(Request $request)
    {
        $order = OrderLead::where('uid_lead', $request->uid_lead)->first();

        if ($request->type == 'manual') {
            $order = OrderManual::where('uid_lead', $request->uid_lead)->first();
        }

        if ($order) {
            $order->update([
                'notes' => $request->notes,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => $request->type . ' Notea berhasil diupdate',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Order tidak ditemukan',
        ], 400);
    }

    public function getApprovalUser(Request $request)
    {
        $warehouse = User::query();
        $warehouse->where('name', 'not like', '%GUARDIAN%');
        if ($request->search) {
            $warehouse->where('name', 'like', '%' . $request->search . '%');
        }

        $warehouses = $warehouse->whereHas('roles', function ($query) {
            $query->whereIn('role_type', ['warehouse', 'admindelivery', 'finance', 'collector', 'lead_finance', 'admin', 'superadmin', 'purchasing']);
        })->get();

        return response()->json([
            'status' => 'success',
            'data' => $warehouses->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->name,
                    'role' => $item->role->role_name,
                    'role_id' => $item->role->id,
                ];
            })
        ]);
    }

    public function getPurchasingUser(Request $request)
    {
        $warehouse = User::query();

        if ($request->search) {
            $warehouse->where('name', 'like', '%' . $request->search . '%');
        }

        $warehouses = $warehouse->whereHas('roles', function ($query) {
            $query->whereIn('role_type', ['purchasing']);
        })->get();

        return response()->json([
            'status' => 'success',
            'data' => $warehouses->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->name,
                    'role' => $item->role->role_name,
                    'role_id' => $item->role->id,
                ];
            })
        ]);
    }

    public function loadUserById(Request $request)
    {
        $user = User::find($request->user_id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'nama' => $user->name,
                'email' => $user->email,
                'phone' => $user->telepon,
                'role' => $user->role,
            ]
        ]);
    }

    public function getTelmarkUserCreated()
    {
        $user = auth()->user();
        if ($user->role->role_type == 'agent-telmar') {
            return response()->json([
                'status' => 'success',
                'data' => [
                    [
                        'id' => $user->id,
                        'name' => $user->name,
                    ]
                ]
            ]);
        }

        $users = DB::table('transactions as tr')->leftJoin('users as u', 'tr.user_create', '=', 'u.id')->whereNotNull('tr.user_create')->groupBy('tr.user_create')->select('u.id', 'u.name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }


    public function getSalesOrder($type)
    {
        $orders = [];
        if ($type == 'lead') {
            $orders = OrderLead::whereStatus(2)->select('id', 'uid_lead', 'order_number', 'invoice_number')->get();
        }

        if ($type == 'manual') {
            $orders = OrderManual::whereType('manual')->whereStatus(2)->select('id', 'uid_lead', 'order_number', 'invoice_number')->get();
        }

        if ($type == 'freebies') {
            $orders = OrderManual::whereType('freebies')->whereStatus(2)->select('id', 'uid_lead', 'order_number', 'invoice_number')->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function getSalesOrderItems($uid_lead)
    {
        $orders = [];
        $orders = OrderDelivery::with('productNeed')->whereUidLead($uid_lead)->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function getPurchaseOrder()
    {
        $orders = [];
        $orders = PurchaseOrder::whereIn('status', [1, 2])->whereHas('items', function ($query) {
            return $query->where('is_master', 1);
        })->select('id', 'po_number')->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function getPurchaseOrderItems($uid_lead)
    {
        $orders = [];
        $orders = PurchaseOrderItem::wherePurchaseOrderId($uid_lead)->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function getCheckbookData()
    {
        $checkbooks = GpCheckbook::all();
        return response()->json([
            'status' => 'success',
            'data' => $checkbooks
        ]);
    }

    public function switchAccount(Request $request)
    {
        try {
            DB::beginTransaction();
            CompanyAccount::where('status', 1)->update(['status' => 0]);
            $account = CompanyAccount::find($request->company_id);
            $username = $account->account_code == '001' ? 'inv' : 'sa';
            $secretcode = $account->account_code == '001' ? 'PT. ANUGRAH INOVASI MAKMUR INDONESIA' : 'Flimty';
            $secretkey = $account->account_code == '001' ? 'uajQfPzUExgkNkD69UL5HE' : '4UhFUi3KyW7VBQ6Jeu9Mm';
            $clientcode = $account->account_code == '001' ? 'CLN00102' : 'CLN00084';


            if ($account->account_code == '001') {
                setSetting('GP_USERNAME_001', $username);
                setSetting('ETHIX_SECRETCODE_001', $secretcode);
                setSetting('ETHIX_SECRETKEY_001', $secretkey);
                setSetting('ETHIX_CLIENTCODE_001', $clientcode);
            } else {
                setSetting('GP_USERNAME_002', $username);
                setSetting('ETHIX_SECRETCODE_002', $secretcode);
                setSetting('ETHIX_SECRETKEY_002', $secretkey);
                setSetting('ETHIX_CLIENTCODE_002', $clientcode);
            }

            $account->update(['status' => 1]);
            CompanyUser::updateOrCreate(['user_id' => auth()->user()->id], ['company_id' => $request->company_id, 'user_id' => auth()->user()->id]);
            DB::commit();

            $pusher = new Pusher(
                'f01866680101044abb79',
                '4327409f9d87bdc35960',
                '1887006',
                [
                    'cluster' => 'ap1',
                    'useTLS' => true
                ]
            );

            $pusher->trigger('bidflow', 'switch-account', ['refresh' => true, 'user_id' => auth()->user()->id]);

            return response()->json([
                'message' => ' Akun berhasil diubah'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'message' => ' Akun gagal diubah.' . $th->getMessage()
            ], 400);
        }
    }

    public function getNotifications()
    {
        $notifications = Notification::where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'DESC');

        return response()->json([
            'message' => 'notifications',
            'data' => array_merge($notifications->paginate(10)->toArray(), ['total_unread' => $notifications->where('status', 0)->count()]),
        ]);
    }

    public function readNotifications(Request $request)
    {
        if ($request->notification_id) {
            $notification = Notification::find($request->notification_id);
            if ($notification) {
                $notification->update(['status' => 1]);
                return response()->json([
                    'message' => 'Read Notification Success',
                    'data' => $notification,
                ]);
            }

            return response()->json([
                'message' => 'Read Notification Failed',
                'data' => null
            ], 400);
        } else {
            try {
                $notification = Notification::where('user_id', auth()->user()->id)->update(['status' => 1]);
                return response()->json([
                    'message' => 'Read Notification Success',
                    'data' => $notification,
                ]);
            } catch (\Throwable $th) {
                return response()->json([
                    'message' => 'Read Notification Failed',
                    'data' => null
                ], 400);
            }
        }
    }

    public function logoutUser()
    {
        // Auth::logout();

        return response()->json([
            'message' => 'Logout berhasil'
        ]);

        return response()->json([
            'message' => 'Logout gagal'
        ], 400);
    }

    public static function generateReferralCode()
    {
        $referralCode = Str::upper(Str::random(5));
        $isDuplicate = User::where('referal_code', $referralCode)->exists();
        while ($isDuplicate) {
            $referralCode = Str::upper(Str::random(5));
            $isDuplicate = User::where('referal_code', $referralCode)->exists();
        }

        return $referralCode;
    }

    public function updateReferal()
    {
        $users = User::whereNull('referal_code')->get();

        foreach ($users as $user) {
            $user->referal_code = $this->generateReferralCode();
            $user->save();
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function getNoKonsi($parent_id)
    {
        if ($parent_id) {
            $order = OrderManual::where('parent_id', $parent_id)->orWhere('id', $parent_id)->get();

            return response()->json([
                'status' => 'success',
                'data' => $order
            ]);
        }
        return response()->json([
            'status' => 'success',
            'data' => []
        ]);
    }

    public function loadPerlengkapan()
    {
        $perlengkapan = ProductAdditional::query()->where('type', 'perlengkapan')->get();

        return response()->json([
            'status' => 'success',
            'data' => $perlengkapan
        ]);
    }

    public function loadProductListVariant()
    {
        $products = DB::table('product_variants')->select('id', 'name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function loadImportValidationData($product_type = 'variants')
    {
        $products = [];

        if ($product_type == 'master') {
            $products = DB::table('products')->where('status', 1)->whereNull('deleted_at')->select('id', 'name')->get();
        }

        if ($product_type == 'variants') {
            $products = DB::table('product_variants')->where('status', 1)->whereNull('deleted_at')->select('id', 'name')->get();
        }

        $warehouses = DB::table('warehouses')->select('id', 'name')->get();
        $payment_terms = DB::table('payment_terms')->select('id', 'name')->get();
        $master_tax = DB::table('master_tax')->select('id', 'tax_code as name')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'products' => $products,
                'warehouses' => $warehouses,
                'payment_terms' => $payment_terms,
                'master_tax' => $master_tax,
            ]
        ]);
    }

    public function loadImportValidationDataUser()
    {
        $users = DB::table('users')->select('id', 'uid', 'name', 'email', 'telepon')->get();

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }


    public function loadImportValidationDataRole()
    {
        $roles = DB::table('roles')->select('id', 'role_name', 'role_type')->get();

        return response()->json([
            'status' => 'success',
            'data' => $roles
        ]);
    }

    public function loadImportValidationDataBrand()
    {
        $brands = DB::table('brands')->select('id', 'name')->get();
        return response()->json([
            'status' => 'success',
            'data' => $brands
        ]);
    }

    public function loadImportValidationDataBin()
    {
        $master_bins = DB::table('master_bins')->select('id', 'name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $master_bins
        ]);
    }

    public function loadImportValidationDataKonsinyasi()
    {
        $order_transfer = DB::table('order_transfers as a')
            ->join('inventory_product_stocks as ips', 'a.uid_lead', '=', 'ips.uid_lead')
            ->where('a.status_so', 0)->where('ips.status', 'done')
            ->select('a.id', 'a.order_number')->get();

        return response()->json([
            'status' => 'success',
            'data' => $order_transfer
        ]);
    }


    public function getSearchBin(Request $request)
    {
        if (isset($request->params['order_type']) && $request->params['order_type'] == 'new') {
            $bin = DB::table('master_bins as mb')
                ->select('mb.id', 'mb.name')
                ->groupBy('mb.id');

            if ($request->search) {
                $bin->where('mb.name', 'like', '%' . $request->search . '%');
            }

            $binData = $bin->limit($request->limit ?? 5)->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama' => $item->name
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => $binData,
            ]);
        }
        $user_list = DB::table('inventory_product_stocks as ips')
            ->join('order_transfers as ot', 'ips.uid_lead', '=', 'ot.uid_lead')
            ->join('master_bins as mb', 'ot.master_bin_id', '=', 'mb.id')
            ->where('ips.status', 'done')
            ->where('ips.inventory_type', 'konsinyasi')
            ->select('ips.id', 'mb.name', 'ot.master_bin_id')
            ->groupBy('ot.master_bin_id');


        if ($request->search) {
            $user_list->where('mb.name', 'like', '%' . $request->search . '%');
        }

        $binData = $user_list->limit($request->limit ?? 5)->get()->map(function ($item) {
            return [
                'id' => $item->master_bin_id,
                'nama' => $item->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $binData,
        ]);
    }

    public function switchDefaultAddress(Request $request)
    {
        try {
            DB::beginTransaction();

            // Set semua alamat user menjadi non-default
            AddressUser::where('user_id', $request->user_id)
                ->update(['is_default' => 0]);

            // Set alamat yang dipilih menjadi default
            $address = AddressUser::where('id', $request->address_id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$address) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Alamat tidak ditemukan'
                ], 404);
            }

            $address->update(['is_default' => 1]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Alamat default berhasil diubah'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengubah alamat default: ' . $th->getMessage()
            ], 400);
        }
    }

    public function deleteProductNeed($type, $id)
    {
        if ($type == 'order') {
            $product = ProductNeed::find($id)->delete();
        } else {
            $product = InventoryDetailItem::find($id)->delete();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil dihapus'
        ]);
    }
}
