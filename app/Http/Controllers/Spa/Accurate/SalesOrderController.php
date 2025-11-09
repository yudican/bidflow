<?php

namespace App\Http\Controllers\Spa\Accurate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalesOrderController extends Controller
{
    /**
     * Create activity log
     */
    private function createActivity($activityType, $title, $description, $userName, $referenceId, $referenceType, $metadata = null, $userId = null)
    {
        try {
            // If userId is not provided, try to get it from the sales order's created_by field
            if ($userId === null && $referenceType === 'sales_order') {
                $salesOrder = DB::connection('pgsql')
                    ->table('sales_orders')
                    ->select('created_by')
                    ->where('id', $referenceId)
                    ->first();
                $userId = $salesOrder ? $salesOrder->created_by : null;
            }
            
            DB::connection('pgsql')->table('activities')->insert([
                'activity_type' => $activityType,
                'title' => $title,
                'description' => $description,
                'user_name' => $userName,
                'user_id' => $userId,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'metadata' => $metadata ? json_encode($metadata) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            error_log('Failed to create activity log: ' . $e->getMessage());
        }
    }

    /**
     * Display the SPA index page
     */
    public function index()
    {
        return view('spa.spa-index');
    }

    /**
     * Get sales orders with pagination and filters
     */
    public function getSalesOrders(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');
        $customerNo = $request->input('customer_no');
        $createdBy = $request->input('created_by');

        $query = DB::connection('pgsql')
            ->table('sales_orders as so')
            ->leftJoin('accurate_customers as ac', 'so.customer_no', '=', 'ac.customer_no')
            ->select(
                'so.id',
                'so.order_number',
                'so.customer_type',
                'so.customer_no',
                'ac.name as customer_name',
                'so.date_transaction',
                'so.delivery_date',
                'so.payment_term_id',
                'so.reference_number',
                'so.branch_customer',
                'so.sub_account_stock',
                'so.is_taxable',
                'so.tax_amount',
                'so.total_tax_amount',
                'so.subtotal',
                'so.total_discount',
                'so.grand_total',
                'so.status',
                'so.created_by',
                'so.created_at',
                'so.updated_at'
            );

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('so.order_number', 'ILIKE', "%{$search}%")
                    ->orWhere('so.reference_number', 'ILIKE', "%{$search}%")
                    ->orWhere('ac.name', 'ILIKE', "%{$search}%")
                    ->orWhere('so.customer_no', 'ILIKE', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->where('so.date_transaction', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('so.date_transaction', '<=', $dateTo);
        }

        if ($status) {
            $query->where('so.status', $status);
        }

        if ($customerNo) {
            $query->where('so.customer_no', $customerNo);
        }
        if ($createdBy) {
            $query->where('so.created_by', $createdBy);
        }

        $data = $query->orderBy('so.created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Sales orders retrieved successfully'
        ]);
    }

    /**
     * Get sales order detail by ID
     */
    public function getSalesOrderDetail($id)
    {
        $salesOrder = DB::connection('pgsql')
            ->table('sales_orders as so')
            ->leftJoin('accurate_customers as ac', 'so.customer_no', '=', 'ac.customer_no')
            ->select(
                'so.*',
                'ac.name as customer_name',
                'ac.email as customer_email',
                'ac.work_phone as customer_phone',
            )
            ->where('so.id', $id)
            ->first();

        if (!$salesOrder) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales order not found'
            ], 404);
        }

        // Get sales order items
        $items = DB::connection('pgsql')
            ->table('sales_order_items as soi')
            ->leftJoin('accurate_items as ai', 'soi.product_id', '=', 'ai.id')
            ->select(
                'soi.*',
                'ai.name as product_name',
                'ai.item_no as product_code',
                'ai.unit1 as product_unit'
            )
            ->where('soi.sales_order_id', $id)
            ->get();

        $salesOrder->items = $items;

        return response()->json([
            'status' => 'success',
            'data' => $salesOrder,
            'message' => 'Sales order detail retrieved successfully'
        ]);
    }

    /**
     * Create new sales order
     */
    public function createSalesOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_no' => 'required|string|max:255',
            'date_transaction' => 'required|date',
            'payment_term_id' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'delivery_address' => 'nullable|string',
            'branch_customer' => 'nullable|string|max:255',
            'sub_account_stock' => 'nullable|in:konsi,non_konsi',
            'is_taxable' => 'boolean',
            'tax_inclusive' => 'boolean',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'created_by' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('pgsql')->beginTransaction();

            // Generate order number
            $orderNumber = $this->generateOrderNumber();

            // Calculate totals
            $calculations = $this->calculateOrderTotals($request->items, $request->input('is_taxable', false), $request->input('tax_amount', 0));

            // Create sales order
            $salesOrderId = DB::connection('pgsql')->table('sales_orders')->insertGetId([
                'order_number' => $orderNumber,
                'customer_type' => $request->input('customer_type', 'Accurate'),
                'customer_no' => $request->customer_no,
                'date_transaction' => $request->date_transaction,
                'payment_term_id' => $request->payment_term_id,
                'reference_number' => $request->reference_number,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'branch_customer' => $request->branch_customer,
                'sub_account_stock' => $request->sub_account_stock,
                'is_taxable' => $request->input('is_taxable', false),
                'tax_inclusive' => $request->input('tax_inclusive', false),
                'tax_amount' => $request->input('tax_amount', 0),
                'total_tax_amount' => $calculations['total_tax_amount'],
                'notes' => $request->notes,
                'subtotal' => $calculations['subtotal'],
                'total_discount' => $calculations['total_discount'],
                'grand_total' => $calculations['grand_total'],
                'status' => $request->status ?? 'draft',
                'created_by' => $request->created_by,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Create sales order items
            foreach ($request->items as $item) {
                $discountPercent = $item['discount_percent'] ?? 0;
                $discountAmount = ($item['price'] * $item['qty'] * $discountPercent) / 100;
                $lineTotal = ($item['price'] * $item['qty']) - $discountAmount;

                // Get product info
                $product = DB::connection('pgsql')
                    ->table('accurate_items')
                    ->select('item_no', 'name')
                    ->where('id', $item['product_id'])
                    ->first();

                DB::connection('pgsql')->table('sales_order_items')->insert([
                    'sales_order_id' => $salesOrderId,
                    'product_id' => $item['product_id'],
                    'product_code' => $product->item_no ?? null,
                    'product_name' => $product->name ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::connection('pgsql')->commit();

            // Create activity log
            $this->createActivity(
                'sales_order',
                'Input data Sales Order [' . $orderNumber . ']',
                'User melakukan input data sales order baru',
                $request->created_by ?? '[User Input]',
                $salesOrderId,
                'sales_order',
                [
                    'order_number' => $orderNumber,
                    'customer_no' => $request->customer_no,
                    'grand_total' => $calculations['grand_total'],
                    'status' => $request->status ?? 'draft',
                    'action' => 'create'
                ]
            );

            return response()->json([
                'status' => 'success',
                'data' => ['id' => $salesOrderId, 'order_number' => $orderNumber],
                'message' => 'Sales order created successfully'
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sales order
     */
    public function updateSalesOrder(Request $request, $id = null)
    {
        // If ID is provided in request body, use it (for rejected orders editing)
        $salesOrderId = $request->input('id', $id);

        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer|exists:pgsql.sales_orders,id',
            'customer_no' => 'required|string|max:255',
            'date_transaction' => 'required|date',
            'payment_term_id' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'delivery_address' => 'nullable|string',
            'branch_customer' => 'nullable|string|max:255',
            'sub_account_stock' => 'nullable|in:konsi,non_konsi',
            'is_taxable' => 'boolean',
            'tax_inclusive' => 'boolean',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('pgsql')->beginTransaction();

            // Check if sales order exists
            $salesOrder = DB::connection('pgsql')
                ->table('sales_orders')
                ->where('id', $salesOrderId)
                ->first();

            if (!$salesOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order not found'
                ], 404);
            }

            // Check if the order can be edited
            if (!in_array($salesOrder->status, ['draft', 'rejected'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order cannot be edited. Only draft or rejected orders can be modified.'
                ], 400);
            }

            // Calculate totals
            $calculations = $this->calculateOrderTotals($request->items, $request->input('is_taxable', false), $request->input('tax_amount', 0));

            // Prepare update data
            $updateData = [
                'customer_no' => $request->customer_no,
                'date_transaction' => $request->date_transaction,
                'payment_term_id' => $request->payment_term_id,
                'reference_number' => $request->reference_number,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'branch_customer' => $request->branch_customer,
                'sub_account_stock' => $request->sub_account_stock,
                'is_taxable' => $request->input('is_taxable', false),
                'tax_inclusive' => $request->input('tax_inclusive', false),
                'tax_amount' => $request->input('tax_amount', 0),
                'total_tax_amount' => $calculations['total_tax_amount'],
                'notes' => $request->notes,
                'subtotal' => $calculations['subtotal'],
                'total_discount' => $calculations['total_discount'],
                'grand_total' => $calculations['grand_total'],
                'updated_at' => now()
            ];

            // If editing a rejected order, reset approval status and set back to draft
            if ($salesOrder->status === 'rejected') {
                $updateData['status'] = 'draft';
                $updateData['approved_by'] = null;
                $updateData['approved_at'] = null;
                $updateData['approval_notes'] = null;
                $updateData['rejection_reason'] = null;
            }

            // Update sales order
            DB::connection('pgsql')->table('sales_orders')
                ->where('id', $salesOrderId)
                ->update($updateData);

            // Delete existing items
            DB::connection('pgsql')->table('sales_order_items')
                ->where('sales_order_id', $salesOrderId)
                ->delete();

            // Create new items
            foreach ($request->items as $item) {
                $discountPercent = $item['discount_percent'] ?? 0;
                $discountAmount = ($item['price'] * $item['qty'] * $discountPercent) / 100;
                $lineTotal = ($item['price'] * $item['qty']) - $discountAmount;

                // Get product info
                $product = DB::connection('pgsql')
                    ->table('accurate_items')
                    ->select('item_no', 'name')
                    ->where('id', $item['product_id'])
                    ->first();

                DB::connection('pgsql')->table('sales_order_items')->insert([
                    'sales_order_id' => $salesOrderId,
                    'product_id' => $item['product_id'],
                    'product_code' => $product->item_no ?? null,
                    'product_name' => $product->name ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::connection('pgsql')->commit();

            // Create activity log
            $this->createActivity(
                'sales_order',
                'Update data Sales Order [' . $salesOrder->order_number . ']',
                'User melakukan update data sales order',
                $request->updated_by ?? '[User Update]',
                $id,
                'sales_order',
                [
                    'order_number' => $salesOrder->order_number,
                    'customer_no' => $request->customer_no,
                    'grand_total' => $calculations['grand_total'],
                    'status' => $salesOrder->status,
                    'action' => 'update'
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Sales order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete sales order
     */
    public function deleteSalesOrder($id)
    {
        try {
            DB::connection('pgsql')->beginTransaction();

            $salesOrder = DB::connection('pgsql')
                ->table('sales_orders')
                ->where('id', $id)
                ->first();

            if (!$salesOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order not found'
                ], 404);
            }

            // Delete items first (due to foreign key constraint)
            DB::connection('pgsql')->table('sales_order_items')
                ->where('sales_order_id', $id)
                ->delete();

            // Delete sales order
            DB::connection('pgsql')->table('sales_orders')
                ->where('id', $id)
                ->delete();

            DB::connection('pgsql')->commit();

            // Create activity log
            $this->createActivity(
                'sales_order',
                'Delete data Sales Order [' . $salesOrder->order_number . ']',
                'User melakukan delete data sales order',
                '[User Delete]',
                $id,
                'sales_order',
                [
                    'order_number' => $salesOrder->order_number,
                    'customer_no' => $salesOrder->customer_no,
                    'grand_total' => $salesOrder->grand_total,
                    'status' => $salesOrder->status,
                    'action' => 'delete'
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Sales order deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sales order status
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,approved,processing,shipped,delivered,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updated = DB::connection('pgsql')->table('sales_orders')
            ->where('id', $id)
            ->update([
                'status' => $request->status,
                'updated_at' => now()
            ]);

        if (!$updated) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales order not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sales order status updated successfully'
        ]);
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        $prefix = 'SO';
        $date = Carbon::now()->format('Ymd');

        // Get the last order number for today
        $lastOrder = DB::connection('pgsql')
            ->table('sales_orders')
            ->where('order_number', 'LIKE', $prefix . $date . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }


    /**
     * Calculate order totals
     */
    private function calculateOrderTotals($items, $isTaxable = false, $taxAmount = 0)
    {
        $subtotal = 0;
        $totalDiscount = 0;

        foreach ($items as $item) {
            $discountPercent = $item['discount_percent'] ?? 0;
            $lineSubtotal = $item['price'] * $item['qty'];
            $lineDiscount = ($lineSubtotal * $discountPercent) / 100;

            $subtotal += $lineSubtotal;
            $totalDiscount += $lineDiscount;
        }

        $netAmount = $subtotal - $totalDiscount;
        $totalTaxAmount = $isTaxable ? ($netAmount * $taxAmount / 100) : 0;
        $grandTotal = $netAmount + $totalTaxAmount;

        return [
            'subtotal' => $subtotal,
            'total_discount' => $totalDiscount,
            'total_tax_amount' => $totalTaxAmount,
            'grand_total' => $grandTotal
        ];
    }

    // ========== VERSION 2 API METHODS (WITH APPROVAL FEATURE) ==========

    /**
     * Get sales orders with pagination and filters (V2)
     */
    public function getSalesOrdersV2(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $status = $request->input('status');
        $customerNo = $request->input('customer_no');
        $approvalStatus = $request->input('status');

        $query = DB::connection('pgsql')
            ->table('sales_orders as so')
            ->leftJoin('accurate_customers as ac', 'so.customer_no', '=', 'ac.customer_no')
            ->leftJoin('contact_group_customer as cgc', 'so.customer_child_no', '=', 'cgc.customer_no')
            ->leftJoin('users as u', 'so.created_by', '=', 'u.id')
            ->select(
                'so.id',
                'so.order_number',
                'so.customer_type',
                'so.customer_no',
                'ac.name as customer_name',
                'so.date_transaction',
                'so.delivery_date',
                'so.payment_term_id',
                'so.reference_number',
                'so.branch_customer',
                'so.sub_account_stock',
                'so.is_taxable',
                'so.tax_amount',
                'so.total_tax_amount',
                'so.subtotal',
                'so.total_discount',
                'so.grand_total',
                'so.status',
                'so.status',
                'so.approved_by',
                'so.approved_at',
                'so.rejection_reason',
                'so.created_by',
                'so.created_at',
                'so.updated_at',
                'so.delivery_address',
                'so.notes',
                'cgc.customer_name as nama_toko',
                'u.name as created_by'
            );

        // Apply filters
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('so.order_number', 'ILIKE', "%{$search}%")
                    ->orWhere('so.reference_number', 'ILIKE', "%{$search}%")
                    ->orWhere('ac.name', 'ILIKE', "%{$search}%")
                    ->orWhere('so.customer_no', 'ILIKE', "%{$search}%");
            });
        }

        if ($dateFrom) {
            $query->where('so.date_transaction', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('so.date_transaction', '<=', $dateTo);
        }

        if ($status) {
            $query->where('so.status', $status);
        }

        if ($customerNo) {
            $query->where('so.customer_no', $customerNo);
        }

        if ($approvalStatus) {
            $query->where('so.status', $approvalStatus);
        }

        $data = $query->orderBy('so.created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => 'Sales orders V2 retrieved successfully'
        ]);
    }

    /**
     * Get sales order detail by ID (V2)
     */
    public function getSalesOrderDetailV2($id)
    {
        $salesOrder = DB::connection('pgsql')
            ->table('sales_orders as so')
            ->leftJoin('accurate_customers as ac', 'so.customer_no', '=', 'ac.customer_no')
            ->select(
                'so.*',
                'ac.name as customer_name',
                'ac.email as customer_email',
                'ac.work_phone as customer_phone',
            )
            ->where('so.id', $id)
            ->first();

        if (!$salesOrder) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sales order not found'
            ], 404);
        }

        // Get sales order items
        $items = DB::connection('pgsql')
            ->table('sales_order_items as soi')
            ->leftJoin('accurate_items as ai', 'soi.product_id', '=', 'ai.id')
            ->select(
                'soi.*',
                'ai.name as product_name',
                'ai.item_no as product_code',
                'ai.unit1 as product_unit'
            )
            ->where('soi.sales_order_id', $id)
            ->get();

        return response()->json([
            'status' => 'success',
            'order' => $salesOrder,
            'items' => $items,
            'message' => 'Sales order detail V2 retrieved successfully'
        ]);
    }

    /**
     * Approve sales order
     */
    public function approveSalesOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'approval_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $salesOrder = DB::connection('pgsql')
                ->table('sales_orders')
                ->where('id', $id)
                ->first();

            if (!$salesOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order not found'
                ], 404);
            }

            if ($salesOrder->status === 'approved') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order already approved'
                ], 400);
            }

            $updated = DB::connection('pgsql')->table('sales_orders')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->user()->name,
                    'approved_at' => now(),
                    'approval_notes' => $request->approval_notes,
                    'rejection_reason' => null,
                    'status' => 'approved', // Auto update status to approved when approved
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to approve sales order'
                ], 500);
            }

            // Create activity log
            $this->createActivity(
                'sales_order',
                'Approve Sales Order [' . $salesOrder->order_number . ']',
                'User melakukan approve sales order',
                auth()->user()->name ?? '[User Approve]',
                $id,
                'sales_order',
                [
                    'order_number' => $salesOrder->order_number,
                    'customer_no' => $salesOrder->customer_no,
                    'grand_total' => $salesOrder->grand_total,
                    'status' => 'approved',
                    'approval_notes' => $request->approval_notes,
                    'action' => 'approve'
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Sales order approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to approve sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject sales order
     */
    public function rejectSalesOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $salesOrder = DB::connection('pgsql')
                ->table('sales_orders')
                ->where('id', $id)
                ->first();

            if (!$salesOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order not found'
                ], 404);
            }

            if ($salesOrder->status === 'rejected') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order already rejected'
                ], 400);
            }

            $updated = DB::connection('pgsql')->table('sales_orders')
                ->where('id', $id)
                ->update([
                    'status' => 'rejected',
                    'approved_by' => auth()->user()->name,
                    'approved_at' => now(),
                    'approval_notes' => null,
                    'rejection_reason' => $request->rejection_reason,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to reject sales order'
                ], 500);
            }

            // Create activity log
            $this->createActivity(
                'sales_order',
                'Reject Sales Order [' . $salesOrder->order_number . ']',
                'User melakukan reject sales order',
                auth()->user()->name ?? '[User Reject]',
                $id,
                'sales_order',
                [
                    'order_number' => $salesOrder->order_number,
                    'customer_no' => $salesOrder->customer_no,
                    'grand_total' => $salesOrder->grand_total,
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                    'action' => 'reject'
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Sales order rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to reject sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sales order V2 (with approval features)
     */
    public function updateSalesOrderV2(Request $request, $id = null)
    {
        // If ID is provided in request body, use it (for rejected orders editing)
        $salesOrderId = $request->input('id', $id);

        $validator = Validator::make($request->all(), [
            'id' => 'nullable|integer|exists:pgsql.sales_orders,id',
            'customer_no' => 'required|string|max:255',
            'date_transaction' => 'required|date',
            'payment_term_id' => 'nullable|string',
            'reference_number' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'delivery_address' => 'nullable|string',
            'branch_customer' => 'nullable|string|max:255',
            'sub_account_stock' => 'nullable|in:konsi,non_konsi',
            'is_taxable' => 'boolean',
            'tax_inclusive' => 'boolean',
            'tax_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::connection('pgsql')->beginTransaction();

            // Check if sales order exists
            $salesOrder = DB::connection('pgsql')
                ->table('sales_orders')
                ->where('id', $salesOrderId)
                ->first();

            if (!$salesOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order not found'
                ], 404);
            }

            // Check if the order can be edited
            if (!in_array($salesOrder->status, ['draft', 'rejected'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sales order cannot be edited. Only draft or rejected orders can be modified.'
                ], 400);
            }

            // Calculate totals
            $calculations = $this->calculateOrderTotals($request->items, $request->input('is_taxable', false), $request->input('tax_amount', 0));

            // Prepare update data
            $updateData = [
                'customer_no' => $request->customer_no,
                'date_transaction' => $request->date_transaction,
                'payment_term_id' => $request->payment_term_id,
                'reference_number' => $request->reference_number,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'branch_customer' => $request->branch_customer,
                'sub_account_stock' => $request->sub_account_stock,
                'is_taxable' => $request->input('is_taxable', false),
                'tax_inclusive' => $request->input('tax_inclusive', false),
                'tax_amount' => $request->input('tax_amount', 0),
                'total_tax_amount' => $calculations['total_tax_amount'],
                'notes' => $request->notes,
                'subtotal' => $calculations['subtotal'],
                'total_discount' => $calculations['total_discount'],
                'grand_total' => $calculations['grand_total'],
                'updated_at' => now()
            ];

            // If editing a rejected order, reset approval status and set back to draft
            if ($salesOrder->status === 'rejected') {
                $updateData['status'] = 'draft';
                $updateData['approved_by'] = null;
                $updateData['approved_at'] = null;
                $updateData['approval_notes'] = null;
                $updateData['rejection_reason'] = null;
            }

            // Update sales order
            DB::connection('pgsql')->table('sales_orders')
                ->where('id', $salesOrderId)
                ->update($updateData);

            // Delete existing items
            DB::connection('pgsql')->table('sales_order_items')
                ->where('sales_order_id', $salesOrderId)
                ->delete();

            // Create new items
            foreach ($request->items as $item) {
                $discountPercent = $item['discount_percent'] ?? 0;
                $discountAmount = ($item['price'] * $item['qty'] * $discountPercent) / 100;
                $lineTotal = ($item['price'] * $item['qty']) - $discountAmount;

                // Get product info
                $product = DB::connection('pgsql')
                    ->table('accurate_items')
                    ->select('item_no', 'name')
                    ->where('id', $item['product_id'])
                    ->first();

                DB::connection('pgsql')->table('sales_order_items')->insert([
                    'sales_order_id' => $salesOrderId,
                    'product_id' => $item['product_id'],
                    'product_code' => $product->item_no ?? null,
                    'product_name' => $product->name ?? null,
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'discount_percent' => $discountPercent,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::connection('pgsql')->commit();

            // Create activity log
            $this->createActivity(
                'sales_order',
                'Update data Sales Order V2 [' . $salesOrder->order_number . ']',
                'User melakukan update data sales order (V2 with approval features)',
                auth()->user()->name ?? '[User Update V2]',
                $salesOrderId,
                'sales_order',
                [
                    'order_number' => $salesOrder->order_number,
                    'customer_no' => $request->customer_no,
                    'grand_total' => $calculations['grand_total'],
                    'status' => $updateData['status'] ?? $salesOrder->status,
                    'was_rejected' => $salesOrder->status === 'rejected',
                    'action' => 'update_v2'
                ]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Sales order updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update sales order: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateItems($id, Request $request)
    {
        try {
            $items = $request->input('items', []);

            if (empty($items)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Items tidak boleh kosong'
                ], 422);
            }

            foreach ($items as $item) {
                DB::connection('pgsql')
                    ->table('sales_order_items')
                    ->where('sales_order_id', $id)
                    ->where('product_id', $item['product_id'])
                    ->update([
                        'qty'              => $item['qty'] ?? 0,
                        'price'            => $item['price'] ?? 0,
                        'discount_percent' => $item['discount_percent'] ?? 0,
                        'discount_amount'  => $item['discount_amount'] ?? 0,
                        'line_total'       => $item['line_total'] ?? 0,
                        'updated_at'       => now(),
                    ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Items berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal update items: ' . $e->getMessage(),
            ], 500);
        }
    }
}
