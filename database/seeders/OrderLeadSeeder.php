<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\LeadMaster;
use App\Models\OrderLead as ModelsOrderLead;
use App\Models\ProductNeed;
use App\Models\ProductVariant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderLeadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();
            for ($i = 0; $i < 5; $i++) {
                $brand_id = rand(1, 7);
                if ($brand_id == 5) {
                    $brand_id = 1;
                }
                $brand = Brand::find($brand_id);
                $brand_name = str_replace(' ', '-', $brand->name);
                $user = User::whereHas('roles', function ($query) {
                    $query->whereIn('role_type', ['member', 'agent', 'subagent']);
                })->inRandomOrder()->first();
                $sales = User::whereHas('roles', function ($query) {
                    $query->whereIn('role_type', ['sales']);
                })->inRandomOrder()->first();
                $data = [
                    'brand_id'  => $brand_id,
                    'title'  => $this->generateTitle($brand_name, 'TEST'),
                    'uid_lead' => hash('crc32', Carbon::now()->format('U')),
                    'contact'  => $user->id,
                    'sales'  => $sales->id,
                    'lead_type'  => 'new',
                    'warehouse_id'  => 1,
                    'payment_term'  => 1,
                    'customer_need'  => 'Order Lead ' . $i,
                    'status'  => 0,
                    'user_created' => '963b12db-5dbf-4cd5-91f7-366b2123ccb9',
                ];

                $row = LeadMaster::create($data);

                $order_number = $this->generateOrderNo();
                $dueDate = Carbon::now()->addDays(7);
                if ($row->paymentTerm) {
                    $dueDate = Carbon::now()->addDays($row->paymentTerm->days_of);
                }
                $data_order = [
                    'brand_id'  => $row->brand_id,
                    'title'  => $row->title,
                    'uid_lead' => $row->uid_lead,
                    'contact'  => $row->contact,
                    'sales'  => $row->sales,
                    'customer_need'  => $row->customer_need,
                    'status'  => 1,
                    'user_created' => $row->user_created,
                    'warehouse_id' => $row->warehouse_id,
                    'payment_term' => $row->payment_term,
                    'order_number' => $order_number,
                    'invoice_number' => $this->generateInvoiceNo(),
                    'due_date' => $dueDate,
                ];
                $order = ModelsOrderLead::create($data_order);

                $product_randoms = ProductVariant::inRandomOrder()->limit(rand(1, 3))->get();
                foreach ($product_randoms as $key => $product) {
                    $data_product_need = [
                        'uid_lead' => $order->uid_lead,
                        'product_id' => $product->id,
                        'qty' => rand(1, 5),
                        'price' => 0,
                        'status' => 1,
                        'discount_id' => rand(0, 1),
                        'tax_id' => rand(0, 1),
                        'user_created' => $order->user_created,
                    ];
                    ProductNeed::create($data_product_need);
                }
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }

    private function generateTitle($brand = 'FLIMTY', $role)
    {
        $date = date('m/Y');
        $title = 'LEAD/' . $brand . '/' . $role . '-' . $date;
        $data = DB::select("SELECT * FROM `tbl_lead_masters` where title like '%$title%' order by id desc limit 0,1");
        $count_code = 8 + strlen($brand) + strlen($role) + strlen($date);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->title, $count_code);
                $next = sprintf("%03d", ((int)$awal + 1));
                $nomor = 'LEAD/' . $brand . '/' . $role . '-' . $date . '/' . $next;
            }
        } else {
            $nomor = 'LEAD/' . $brand . '/' . $role . '-' . $date . '/' . '001';
        }
        return $nomor;
    }

    private function generateInvoiceNo()
    {
        $year = date('Y');
        $invoice_number = 'SI/' . $year . '/';
        $data = DB::select("SELECT * FROM `tbl_order_leads` where invoice_number like '%$invoice_number%' order by id desc limit 0,1");
        $count_code = 8 + strlen($year);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->invoice_number, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'SI/' . $year . '/' . $next;
            }
        } else {
            $nomor = 'SI/' . $year . '/' . '000000001';
        }
        return $nomor;
    }

    private function generateOrderNo()
    {
        $year = date('Y');
        $order_number = 'SO/' . $year . '/';
        $data = DB::select("SELECT * FROM `tbl_order_leads` where order_number like '%$order_number%' order by id desc limit 0,1");
        $count_code = 8 + strlen($year);
        $total = count($data);
        if ($total > 0) {
            foreach ($data as $rw) {
                $awal = substr($rw->order_number, $count_code);
                $next = sprintf("%09d", ((int)$awal + 1));
                $nomor = 'SO/' . $year . '/' . $next;
            }
        } else {
            $nomor = 'SO/' . $year . '/' . '000000001';
        }
        return $nomor;
    }
}
