<?php

namespace App\Http\Controllers\Spa\Accurate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccurateCustomerController extends Controller
{
  public function index()
  {
    return view('spa.spa-index');
  }

  public function getCustomers(Request $request)
  {
    $perPage = $request->input('per_page', 20);
    $search = $request->input('search');

    $query = DB::connection('pgsql')
      ->table('accurate_customers')
      ->select([
        'id',
        'accurate_id',
        'name',
        'email',
        'customer_no',
        'category_name',
        'npwp_no',
        'work_phone',
        'ship_city',
        'warehouse_name',
        'created_at'
      ]);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('name', 'ILIKE', "%{$search}%")
          ->orWhere('customer_no', 'ILIKE', "%{$search}%")
          ->orWhere('email', 'ILIKE', "%{$search}%")
          ->orWhere('npwp_no', 'ILIKE', "%{$search}%");
      });
    }

    $customers = $query->orderBy('name', 'asc')->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'data' => $customers
    ]);
  }

  public function getCustomerSubaccounts(Request $request)
  {
    $perPage = $request->input('per_page', 20);
    $search = $request->input('search');

    // if (!$customerNo) {
    //   return response()->json(['status' => 'error', 'message' => 'customer_no is required'], 400);
    // }

    // $group = DB::connection('pgsql')->table('contact_groups')->first();
    // if (!$group) {
    //   return response()->json(['status' => 'error', 'message' => 'Contact group not found'], 404);
    // }

    $query = DB::connection('pgsql')->table('contact_group_customer')

      ->join('contact_groups', 'contact_group_customer.contact_group_id', '=', 'contact_groups.id')
      ->leftJoin('accurate_customers', 'contact_groups.customer_no', '=', 'accurate_customers.customer_no')
      ->select([
        'contact_group_customer.contact_group_id',
        'contact_group_customer.accurate_customer_id',
        'accurate_customers.customer_no as customer_head_no',
        'contact_group_customer.customer_no as customer_child_no',
        'accurate_customers.name as customer_head_name',
        'accurate_customers.email as customer_head_email',
        // 'contact_group_customer.customer_name as customer_head_name',
        // 'contact_group_customer.customer_email as customer_head_email',
        'contact_group_customer.customer_name as customer_child_name',
        'contact_group_customer.customer_email as customer_child_email',
        'accurate_customers.work_phone as customer_head_phone',
        'accurate_customers.ship_city as customer_head_city',
        'contact_group_customer.prov as customer_sub_province',
        'contact_group_customer.kab_kota as customer_sub_city',
        'contact_group_customer.alamat as customer_address',
        'accurate_customers.warehouse_name as customer_head_warehouse',
        'accurate_customers.created_at as customer_head_created_at',
        'accurate_customers.updated_at as customer_head_updated_at',
        'contact_group_customer.created_at',
        'contact_group_customer.updated_at'
      ]);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('contact_group_customer.customer_no', 'ILIKE', "%{$search}%")
          ->orWhere('contact_group_customer.customer_name', 'ILIKE', "%{$search}%");
      });
    }

    $subaccounts = $query->orderBy('contact_group_customer.customer_name', 'asc')->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'data' => $subaccounts
    ]);
  }
}
