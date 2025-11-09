<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\Cases;
use App\Models\InventoryItem;
use App\Models\LeadActivity;
use App\Models\LeadMaster;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\Product;
use App\Models\ProductNeed;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\RefundItem;
use App\Models\RefundMaster;
use App\Models\ReturMaster;
use App\Models\SalesReturn;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\TransactionDetail;
use App\Models\User;
use App\Models\Whislist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index($detail = null)
    {
        return view('spa.spa-index');
    }

    public function detailDashboard(Request $request)
    {
        $type = $request->type; // customer || agent || lead
        $product_need = ProductNeed::with(['product'])->groupBy('product_id')->limit(5)->get();

        $top_rate = TransactionDetail::query()->with(['product'])->whereHas('transaction', function ($query) {
            return $query->whereHas('commentRating', function ($q) {
                return $q->orderBy('rate', 'desc');
            });
        })->groupBy('product_id')->limit(5)->get();

        $top_product = TransactionDetail::query()->with(['product'])->whereHas('transaction', function ($query) {
            return $query->orderBy('product_id', 'desc');
        })->groupBy('product_id')->limit(5)->get();

        $top_like = Whislist::query()->with(['product'])->whereHas('product', function ($query) {
            return $query->selectRaw('tbl_products.*, count(product_id) as count_like');
        })->groupBy('product_id')->limit(5)->get();

        $purchase_vendor = PurchaseOrder::groupBy('vendor_name')->limit(5)->get();

        $product_inventory = InventoryItem::query()->with(['product'])->groupBy('product_id')->limit(5)->get();

        $product_stock = ProductVariant::groupBy('product_id')->get();

        $product_restock = [];
        $available_product = 0;
        $product_restocks = ProductVariant::all();
        foreach ($product_restocks as $key => $value) {
            if ($key <= 5) {
                if ($value->stock <= 5) {
                    $product_restock[] = $value;
                }
            }

            if ($value->stock > 5) {
                $available_product += 1;
            }
        }

        $product_need_restock = [];
        $available_need_product = 0;
        $product_need_restock = ProductNeed::with(['product'])->groupBy('product_id')->get();
        foreach ($product_need_restock as $key => $value) {
            if ($key <= 5) {
                if ($value->stock <= 5) {
                    $product_need_restock[] = $value;
                }
            }

            if ($value->stock > 5) {
                $available_need_product += 1;
            }
        }

        $role = $request->type == 'custommer' ? ['member'] : ['agent', 'subagent'];
        $total_customer = User::whereHas('roles', function ($query) use ($role) {
            return $query->whereIn('role_type', $role);
        })->count();
        $total_member = User::whereHas('roles', function ($query) {
            return $query->where('role_type', 'member');
        })->count();
        $lead_active_create = LeadMaster::where('status', 0)->count();
        $lead_active_waiting = LeadMaster::where('status', 2)->count();
        $lead_qualified = LeadMaster::where('status', 1)->count();
        $lead_unqualified = LeadMaster::where('status', 3)->count();
        $activity_inprogress = LeadActivity::where('status', 1)->count();
        $activity_open = LeadActivity::where('status', 2)->count();
        $activity_completed = LeadActivity::where('status', 3)->count();
        $activity_canceled = LeadActivity::where('status', 4)->count();
        $purchase_all = PurchaseOrder::count() ?? 1;
        $purchase_product = (PurchaseOrder::where('type_po', 'product')->count() / $purchase_all) * 100;
        $purchase_pengemasan = (PurchaseOrder::where('type_po', 'pengemasan')->count() / $purchase_all) * 100;
        $purchase_perlengkapan = (PurchaseOrder::where('type_po', 'perlengkapan')->count() / $purchase_all) * 100;
        $purchase_waiting_rev = PurchaseOrder::where('status', 5)->count();
        $purchase_waiting = PurchaseOrder::where('status', 5)->count();
        $purchase_proses = PurchaseOrder::where('status', 1)->count();
        $purchase_complete = PurchaseOrder::where('status', 7)->count();
        $purchase_delivery = PurchaseOrder::where('status', 2)->count();
        $purchase_draft = PurchaseOrder::where('status', 1)->count();


        if ($type == 'lead') {
            $total_retur = SalesReturn::count();
            // $refund = RefundItem::leftjoin('prices', 'refund_items.product_id', 'prices.product_id')->where('prices.level_id', 4)->get();
            // $total_refund = 0;
            // foreach ($refund as $ref) {
            //     $total_refund += $ref->final_price;
            // }
            $total_refund = RefundMaster::count();
            $total_order_lead = 0;
            $total_order_manual = 0;
            $debt_order_leads = OrderLead::where('status', 2)->get();
            foreach ($debt_order_leads as $key => $value) {
                $total_order_lead += $value->amount;
            }

            $debt_order_manuals = OrderManual::where('status', 2)->get();
            foreach ($debt_order_manuals as $key => $value) {
                $total_order_manual += $value->amount;
            }
            $total_debt = $total_order_lead + $total_order_manual;
            $orlead = OrderLead::where('status', 2)->count();
            $order_new = OrderLead::where('status', 1)->count();
            $order_open = OrderLead::where('status', 2)->count();
            $order_closed = OrderLead::where('status', 3)->count();
            $order_cancel = OrderLead::where('status', 4)->count();
            $ormanual = OrderManual::where('status', 2)->count();
            $caseManual = Cases::count();
            $unpaid_inv = $orlead + $ormanual;
            $total_agent = User::whereHas('roles', function ($query) {
                return $query->where('role_type', 'subagent');
            })->count();
            $total_distributor = User::whereHas('roles', function ($query) {
                return $query->where('role_type', 'agent');
            })->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_order_amount' => 0,
                    'total_amount' => 0,
                    'total_order' => 0,
                    'transaction_active' => 0,
                    'waiting_payment' => 0,
                    'total_retur' => $total_retur,
                    'total_refund' => $total_refund,
                    'total_debt_amount' => $total_debt,
                    'total_order_lead' => OrderLead::count(),
                    'total_order_manual' => OrderManual::count(),
                    'total_unpaid_invoice' => $unpaid_inv,
                    'total_agent' => $total_agent,
                    'total_distributor' => $total_distributor,
                    'total_case_manual' => $caseManual,
                    // 'refunds' => $refund,
                    'product_need' => $product_need,
                    'product_performance' => [],
                    'charts' => [
                        'lead' => [
                            'total_lead_unqualified' => $lead_unqualified,
                            'total_lead_qualified' => $lead_qualified,
                        ],
                        'lead_order_by_stage' => [
                            'total_order_new' => $order_new,
                            'total_order_open' => $order_open,
                            'total_order_closed' => $order_closed,
                            'total_order_cancel' => $order_cancel,
                        ],
                        'lead_by_stage' => [
                            'total_lead_active_create' => $lead_active_create,
                            'total_activity_inprogress' => $activity_inprogress,
                            'total_lead_active_waiting' => $lead_active_waiting,
                            'total_lead_qualified' => $lead_qualified,
                        ],
                        'lead_order' => [
                            'total_order_lead' => $orlead,
                            'total_order_manual' => $ormanual,
                        ]
                    ]
                ]
            ], 200);
        }

        $total_amount_income = '0';
        $total_order_amount = '0';
        $total_order = '0';
        $transaction_active = '0';
        $waiting_payment = '0';

        if ($type != 'finance' && $type != 'warehouse' && $type != 'admindelivery') {
            $total_amount_income = $this->getTransaction($type)->where('status_delivery', 4)->sum('amount_to_pay');
            $total_order_amount = $this->getTransaction($type)->sum('amount_to_pay');
            $total_order = $this->getTransaction($type)->count();
            $transaction_active = $this->getTransaction($type)->where('status_delivery', '>', 2)->count();
            $waiting_payment = $this->getTransaction($type)->where('status', '<', 3)->count();
        }

        // product performance
        $product_performs = [];
        $product_variants = ProductVariant::all();
        $variants = [];
        for ($i = 1; $i <= 12; $i++) {
            $products = [];
            $products['name'] = $this->getMonthName($i);
            foreach ($product_variants as $key => $variant) {
                if (!isset($variants[$key])) {
                    $variants[] = ['id' => $variant->id, 'name' => $variant->name];
                }
                $product = TransactionDetail::where('product_id', $variant->product_id)->whereHas('transaction', function ($query) use ($i) {
                    return $query->whereYear('created_at', '=', date('Y'))->whereMonth('created_at', $i);
                });

                $products['product_' . $variant->id] = [
                    'id' => $variant->id,
                    'label' => $variant->name,
                    'total' => intval($product->sum('qty')),
                ];
            }

            $product_performs[] = $products;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'product_ids' => $variants,
                'total_amount_income' => $total_amount_income,
                'total_order_amount' => $total_order_amount,
                'top_rate' => $top_rate,
                'top_product' => $top_product,
                'top_like' => $top_like,
                'total_order' => $total_order,
                'transaction_active' => $transaction_active,
                'waiting_payment' => $waiting_payment,
                'available_product' => $available_product,
                'product_restock' => $product_restock,
                'total_customer' => $total_customer,
                'total_member' => $total_member,
                'lead_active_create' => $lead_active_create,
                'lead_active_waiting' => $lead_active_waiting,
                'lead_qualified' => $lead_qualified,
                'lead_unqualified' => $lead_unqualified,
                'activity_inprogress' => $activity_inprogress,
                'activity_open' => $activity_open,
                'activity_completed' => $activity_completed,
                'activity_canceled' => $activity_canceled,
                'product_need' => $product_need,
                'product_performance' => $product_performs,
                'purchase_product' => $purchase_product,
                'purchase_pengemasan' => $purchase_pengemasan,
                'purchase_perlengkapan' => $purchase_perlengkapan,
                'purchase_all' => $purchase_all,
                'purchase_waiting' => $purchase_waiting,
                'purchase_proses' => $purchase_proses,
                'purchase_complete' => $purchase_complete,
                'purchase_vendor' => $purchase_vendor,
                'product_inventory' => $product_inventory,
                'product_stock' => $product_stock,
                'product_need_restock' => $product_need_restock,
                'charts' => [
                    'transaction' => [
                        'total_transaction_active' => $transaction_active,
                        'total_waiting_payment' => $waiting_payment
                    ],
                    'lead' => [
                        'total_lead_active_create' => $lead_active_create,
                        'total_lead_active_waiting' => $lead_active_waiting,
                    ],
                    'lead_by_stage' => [
                        'total_lead_qualified' => $lead_qualified,
                        'total_lead_unqualified' => $lead_unqualified,
                        'total_lead_qualified' => $lead_qualified,
                        'total_activity_inprogress' => $activity_inprogress,
                    ],
                    'purchase_by_stage' => [
                        'draft' => $purchase_draft,
                        'waiting' => $purchase_waiting_rev,
                        'on_process' => $purchase_proses,
                        'delivery' => $purchase_delivery,
                        'completed' => $purchase_complete,
                    ],
                    'activity_by_lead' => [
                        'total_activity_open' => $activity_open,
                        'total_activity_completed' => $activity_completed,
                        'total_activity_canceled' => $activity_canceled,
                        'total_activity_inprogress' => $activity_inprogress,
                    ]
                ]
            ]
        ]);
    }

    public function getTransaction($type = 'custommer')
    {
        switch ($type) {
            case 'custommer':
                return Transaction::query();
            case 'agent':
                return TransactionAgent::query();

                // default:
                //     return TransactionAgent::query();
        }
    }

    public function getMonthName($month)
    {
        $monthName = date("F", mktime(0, 0, 0, $month, 10));
        return $monthName;
    }
}
