<?php

namespace App\Http\Livewire;

use App\Jobs\ClearCache;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\TransactionDetail;
use App\Models\Product;
use App\Models\User_data;
use App\Models\Whislist;
use App\Models\Brand;
use App\Models\CommentRating;
use App\Models\LeadMaster;
use App\Models\LeadActivity;
use App\Models\ProductNeed;
use Illuminate\Support\Facades\Artisan;

class DashboardAgent extends Component
{
    public $transaction_active = 0;
    public $waiting_payment = 0;
    public $available_product = 0;
    public $total_complete = 0;
    public $total_customer = 0;
    public $lead_active_create = 0;
    public $lead_active_waiting = 0;
    public $lead_qualified = 0;
    public $lead_unqualified = 0;
    public $product_need;
    public $activity_inprogress = 0;
    public $activity_open = 0;
    public $activity_completed = 0;
    public $activity_canceled = 0;
    public $product_restock = [];

    public function mount()
    {

        $transaction = TransactionAgent::select('amount_to_pay')->where('status_delivery', 4)->get();
        if (!empty($transaction)) {
            foreach ($transaction as $trans) {
                $this->total_complete += $trans->amount_to_pay;
            }
        }
        $this->top_rate = TransactionDetail::query()->whereHas('transaction', function ($query) {
            return $query->whereHas('commentRating', function ($q) {
                return $q->orderBy('rate', 'desc');
            });
        })->groupBy('product_id')->limit(5)->get();

        $this->top_product = TransactionDetail::query()->whereHas('transaction', function ($query) {
            return $query->orderBy('product_id', 'desc');
        })->groupBy('product_id')->limit(5)->get();

        $this->top_like = Whislist::query()->whereHas('product', function ($query) {
            return $query->selectRaw('tbl_products.*, count(product_id) as count_like');
            // return $query->orderBy('product_id', 'desc');
            // return $query->whereHas('commentRating', function ($q) {
            //     return $q->orderBy('rate', 'desc');
            // });
        })->groupBy('product_id')->limit(5)->get();

        // $month = [1,2,3,4,5,6,7,8,9,10,11,12];
        $arr_prod = [];
        for ($i = 1; $i <= 12; $i++) {
            $product_perform = Product::leftjoin('transaction_details', 'transaction_details.product_id', '=', 'products.id')
                ->leftjoin('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                ->select('products.name', DB::raw('sum(qty) as `total_qty`'), DB::raw('YEAR(tbl_transactions.created_at) as `year`, MONTH(tbl_transactions.created_at) as `month`'))
                ->groupby('year', 'month')->whereYear('transactions.created_at', '=', date('Y'))->whereMonth('transactions.created_at', '=', $i)->get();
            if (!empty($product_perform)) {
                array_push($arr_prod, $product_perform);
            } else {
                $product_perform = ["name" => "", "total_qty" => 0, "year" => date('Y'), "month" => $i];
                array_push($arr_prod, $product_perform);
            }
        }
        $this->product_perform = $arr_prod;
        // $this->product_perform = Product::leftjoin('transaction_details', 'transaction_details.product_id', '=', 'products.id')
        //         ->leftjoin('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
        //         ->select('products.name', DB::raw('sum(qty) as `total_qty`'), DB::raw('YEAR(tbl_transactions.created_at) as `year`, MONTH(tbl_transactions.created_at) as `month`'))
        //         ->groupby('year','month')->whereYear('transactions.created_at', '=', date('Y'))->whereMonth('transactions.created_at', '=', 3)->get();

        $days = array();
        $days[0] = date('j M');
        $days[1] = date('j M', strtotime("-1 days"));
        $days[2] = date('j M', strtotime("-2 days"));
        $days[3] = date('j M', strtotime("-3 days"));
        $days[4] = date('j M', strtotime("-4 days"));
        $days[5] = date('j M', strtotime("-5 days"));
        $days[6] = date('j M', strtotime("-6 days"));
        $days[7] = date('j M', strtotime("-7 days"));
        $days[8] = date('j M', strtotime("-8 days"));
        $days[9] = date('j M', strtotime("-9 days"));
        $days[10] = date('j M', strtotime("-10 days"));
        $days[11] = date('j M', strtotime("-11 days"));
        $days[12] = date('j M', strtotime("-12 days"));
        $days[13] = date('j M', strtotime("-13 days"));
        $days[14] = date('j M', strtotime("-14 days"));
        $days[15] = date('j M', strtotime("-15 days"));
        $days[16] = date('j M', strtotime("-16 days"));
        $days[17] = date('j M', strtotime("-17 days"));
        $days[18] = date('j M', strtotime("-18 days"));
        $days[19] = date('j M', strtotime("-19 days"));
        $days[20] = date('j M', strtotime("-20 days"));
        $days[21] = date('j M', strtotime("-21 days"));
        $days[22] = date('j M', strtotime("-22 days"));
        $days[23] = date('j M', strtotime("-23 days"));
        $days[24] = date('j M', strtotime("-24 days"));
        $days[25] = date('j M', strtotime("-25 days"));
        $days[26] = date('j M', strtotime("-26 days"));
        $days[27] = date('j M', strtotime("-27 days"));
        $days[28] = date('j M', strtotime("-28 days"));
        $days[29] = date('j M', strtotime("-29 days"));
        $days[30] = date('j M', strtotime("-30 days"));

        $label30day = array($days[30], $days[29], $days[28], $days[27], $days[26], $days[25], $days[24], $days[23], $days[22], $days[21], $days[20], $days[19], $days[18], $days[17], $days[16], $days[15], $days[14], $days[13], $days[12], $days[11], $days[10], $days[9], $days[8], $days[7], $days[6], $days[5], $days[4], $days[3], $days[2], $days[1], $days[0]);
        $this->datatanggal = $label30day;

        $dataAllRevenueAllSdc = array();
        $brand = Brand::all();
        foreach ($brand as $sdc) {
            $entry = new ERecordAgent;
            $entry->name = $sdc->name;
            $entry->data = Transaction::totalAllRevenueThisWeek($sdc->id);
            $dataAllRevenueAllSdc[] = $entry;
        }

        $this->datareportperform = $dataAllRevenueAllSdc;

        $this->product_restock = Product::where('stock', '<=', 5)->whereNull('deleted_at')->limit(5)->get();
        // $this->top_like = Whislist::groupBy('product_id')->selectRaw('tbl_products.*, count(product_id) as count_like')->leftjoin('products', 'whislists.product_id', '=', 'products.id')->limit(5)->get();
        // $this->top_product = TransactionDetail::groupBy('transaction_details.product_id')->selectRaw('tbl_products.*, sum(qty) as sum_qty')->leftjoin('products', 'transaction_details.product_id', '=', 'products.id')->limit(5)->where('products.deleted_at', null)->get();
        $this->route_name = request()->route()->getName();
        $this->total_order = Transaction::get()->count();
        $this->transaction_active = TransactionAgent::where('status_delivery', '<', 4)->get()->count();
        $this->waiting_payment = TransactionAgent::where('status', '<=', 2)->get()->count();
        $this->available_product = Product::where('stock', '>', 5)->get()->count();
        $this->total_customer = User_data::all()->count();
        $this->lead_active_create = LeadMaster::where('status', 0)->get()->count();
        $this->lead_active_waiting = LeadMaster::where('status', 2)->get()->count();
        $this->lead_qualified = LeadMaster::where('status', 1)->get()->count();
        $this->lead_unqualified = LeadMaster::where('status', 3)->get()->count();
        $this->activity_inprogress = LeadActivity::where('status', 1)->get()->count();
        $this->activity_open = LeadActivity::where('status', 2)->get()->count();
        $this->activity_completed = LeadActivity::where('status', 3)->get()->count();
        $this->activity_canceled = LeadActivity::where('status', 4)->get()->count();
        $this->product_need = ProductNeed::leftjoin('products', 'product_needs.product_id', '=', 'products.id')->select('product_needs.*', 'products.name')->limit(5)->where('products.deleted_at', null)->get();
    }

    public function render()
    {
        // $product = Product::groupBy('product_id')->selectRaw('tbl_products.*, count(product_id) as count_rate')->leftjoin('products', 'comment_ratings.product_id', '=', 'products.id')->limit(5)->get();
        // $product = Product::where('stock', '<=', 5)->limit(5)->get();
        // ->select(DB::raw('count(id) as `data`'), DB::raw("DATE_FORMAT(created_at, '%m-%Y') new_date"),  DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
        // ->groupby('year','month')
        // ->get();

        // $arr_prod = [];
        // for ($i=1;$i<=12;$i++) {
        //     $product_perform = Product::leftjoin('transaction_details', 'transaction_details.product_id', '=', 'products.id')
        //         ->leftjoin('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
        //         ->select('products.name', DB::raw('sum(qty) as `total_qty`'), DB::raw('YEAR(tbl_transactions.created_at) as `year`, MONTH(tbl_transactions.created_at) as `month`'))
        //         ->groupby('year','month')->whereYear('transactions.created_at', '=', date('Y'))->whereMonth('transactions.created_at', '=', $i)->get();
        //     if (!empty($product_perform)) {
        //         array_push($arr_prod, $product_perform);
        //     } else {
        //         $product_empty = ["name"=>"-", "total_qty"=>0, "year"=>date('Y'), "month" =>$i];
        //         array_push($arr_prod, $product_empty);
        //     }
        // }
        // $top_product = TransactionDetail::groupBy('transaction_details.product_id')->selectRaw('tbl_products.*, sum(qty) as sum_qty')->leftjoin('products', 'transaction_details.product_id', '=', 'products.id')->limit(5)->where('products.deleted_at', null)->get();
        // echo"<pre>";print_r($top_product);die();
        return view('livewire.dashboard-agent');
    }

    public function init()
    {
        // ClearCache::dispatch()->onQueue('queue-log');
    }
}

class ERecordAgent
{
    /**
     * Name of the records
     * @var string
     */
    public $name;

    /**
     * Set of data
     *
     * @var array
     */
    public $data;
}
