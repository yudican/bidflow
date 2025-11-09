<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\GpCustomer;
use Illuminate\Http\Request;

class GPCustomerController extends Controller
{
    public function index($customer_id = null)
    {
        return view('spa.spa-index');
    }

    public function customerList(Request $request)
    {
        $orderList = GpCustomer::query();
        $orders = $orderList->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    // create new customer
    public function createCustomer(Request $request)
    {
        $customer = GpCustomer::create([
            'customer_id' => $request->customer_id,
            'customer_name' => $request->customer_name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
        ]);
    }

    // update customer
    public function updateCustomer(Request $request, $customer_id)
    {
        $customer = GpCustomer::find($customer_id);
        $customer->update([
            'customer_id' => $request->customer_id,
            'customer_name' => $request->customer_name,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully',
        ]);
    }

    // delete customer
    public function deleteCustomer(Request $request, $customer_id)
    {
        $customer = GpCustomer::find($customer_id);
        $customer->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully',
        ]);
    }
}
