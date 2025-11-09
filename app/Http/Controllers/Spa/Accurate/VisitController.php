<?php

namespace App\Http\Controllers\Spa\Accurate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VisitController extends Controller
{
    /**
     * Get list of visits with pagination and filtering
     */
    public function getVisits(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $status = $request->get('status');
            $customerId = $request->get('customer_id');
            $customerChildId = $request->get('customer_child_id');

            $query = DB::connection('pgsql')
                ->table('visit_lists as vl')
                ->leftJoin('contact_group_customer as cgc', 'vl.customer_id', '=', 'cgc.customer_no')
                ->leftJoin('contact_group_customer as cgc_child', 'vl.customer_child_id', '=', 'cgc_child.customer_no')
                ->select([
                    'vl.*',
                    'cgc.customer_name',
                    'cgc_child.customer_name as customer_child_name'
                ]);

            // Apply filters
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('vl.visit_id', 'ILIKE', "%{$search}%")
                      ->orWhere('vl.pic_name', 'ILIKE', "%{$search}%")
                      ->orWhere('cgc.customer_name', 'ILIKE', "%{$search}%")
                      ->orWhere('cgc_child.customer_name', 'ILIKE', "%{$search}%");
                });
            }

            if (!empty($dateFrom)) {
                $query->where('vl.date', '>=', $dateFrom);
            }

            if (!empty($dateTo)) {
                $query->where('vl.date', '<=', $dateTo);
            }

            if (!empty($status)) {
                $query->where('vl.status', $status);
            }

            if (!empty($customerId)) {
                $query->where('vl.customer_id', $customerId);
            }

            if (!empty($customerChildId)) {
                $query->where('vl.customer_child_id', $customerChildId);
            }

            $total = $query->count();
            $visits = $query->orderBy('vl.created_at', 'desc')
                           ->offset(($request->get('page', 1) - 1) * $perPage)
                           ->limit($perPage)
                           ->get();

            return response()->json([
                'status' => 'success',
                'data' => $visits,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $request->get('page', 1),
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get visits: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get visit detail with items
     */
    public function getVisitDetail($visitId)
    {
        try {
            $visit = DB::connection('pgsql')
                ->table('visit_lists as vl')
                ->leftJoin('contact_group_customer as cgc', 'vl.customer_id', '=', 'cgc.customer_no')
                ->leftJoin('contact_group_customer as cgc_child', 'vl.customer_child_id', '=', 'cgc_child.customer_no')
                ->select([
                    'vl.*',
                    'cgc.customer_name',
                    'cgc_child.customer_name as customer_child_name'
                ])
                ->where('vl.visit_id', $visitId)
                ->first();

            if (!$visit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Visit not found'
                ], 404);
            }

            // Get visit items
            $visitItems = DB::connection('pgsql')
                ->table('visit_items')
                ->where('visit_list_id', $visit->id)
                ->get();

            $visit->items = $visitItems;

            return response()->json([
                'status' => 'success',
                'data' => $visit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get visit detail: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new visit
     */
    public function createVisit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date',
                'customer_id' => 'required|string',
                'customer_child_id' => 'nullable|string',
                'pic_name' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.qty_visit_plan' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::connection('pgsql')->beginTransaction();

            // Generate visit_id
            $visitId = 'VST-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create visit
            $visitListId = DB::connection('pgsql')
                ->table('visit_lists')
                ->insertGetId([
                    'visit_id' => $visitId,
                    'date' => $request->date,
                    'customer_id' => $request->customer_id,
                    'customer_child_id' => $request->customer_child_id,
                    'pic_name' => $request->pic_name,
                    'notes' => $request->notes,
                    'status' => 'draft',
                    'created_by' => auth()->user()->name ?? 'system',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

            // Create visit items
            foreach ($request->items as $item) {
                DB::connection('pgsql')
                    ->table('visit_items')
                    ->insert([
                        'visit_list_id' => $visitListId,
                        'qty_visit_plan' => $item['qty_visit_plan'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
            }

            DB::connection('pgsql')->commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Visit created successfully',
                'data' => ['visit_id' => $visitId]
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create visit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update visit status
     */
    public function updateVisitStatus(Request $request, $visitId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:draft,submitted'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updated = DB::connection('pgsql')
                ->table('visit_lists')
                ->where('visit_id', $visitId)
                ->update([
                    'status' => $request->status,
                    'updated_at' => now()
                ]);

            if (!$updated) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Visit not found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Visit status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update visit status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete visit
     */
    public function deleteVisit($visitId)
    {
        try {
            $visit = DB::connection('pgsql')
                ->table('visit_lists')
                ->where('visit_id', $visitId)
                ->first();

            if (!$visit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Visit not found'
                ], 404);
            }

            DB::connection('pgsql')->beginTransaction();

            // Delete visit items first (due to foreign key constraint)
            DB::connection('pgsql')
                ->table('visit_items')
                ->where('visit_list_id', $visit->id)
                ->delete();

            // Delete visit
            DB::connection('pgsql')
                ->table('visit_lists')
                ->where('id', $visit->id)
                ->delete();

            DB::connection('pgsql')->commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Visit deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::connection('pgsql')->rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete visit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get visit statistics for dashboard
     */
    public function getVisitStatistics(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            // Get aggregated data per PIC using created_by to get user data
            $visitData = DB::connection('pgsql')
                ->table('visit_lists as vl')
                ->leftJoin('contact_group_customer as cgc', 'vl.customer_id', '=', 'cgc.customer_no')
                ->leftJoin('contact_group_customer as cgc_child', 'vl.customer_child_id', '=', 'cgc_child.customer_no')
                ->select([
                    'vl.pic_name',
                    'vl.created_by',
                    DB::raw("COUNT(DISTINCT COALESCE(cgc.customer_name, cgc_child.customer_name)) as total_store_assigned"),
                    DB::raw("COUNT(vl.id) as total_visit_store"),
                    DB::raw("CASE 
                        WHEN COUNT(CASE WHEN vl.status = 'submitted' THEN 1 END) = COUNT(vl.id) THEN 'Completed'
                        WHEN COUNT(CASE WHEN vl.status = 'draft' THEN 1 END) > 0 THEN 'Pending'
                        ELSE 'Unknown'
                    END as status")
                ])
                ->groupBy('vl.pic_name', 'vl.created_by')
                ->orderBy('vl.pic_name', 'asc')
                ->get();

            // Process data to get role information from User model
            $processedData = [];
            foreach ($visitData as $visit) {
                // Get user role using model
                $user = \App\Models\User::find($visit->created_by);
                $role = 'Staff'; // Default role
                
                if ($user && $user->role) {
                    $role = $user->role->role_name ?? 'Staff';
                }

                // Apply search filter
                if (!empty($search)) {
                    $searchLower = strtolower($search);
                    if (strpos(strtolower($visit->pic_name), $searchLower) === false && 
                        strpos(strtolower($role), $searchLower) === false) {
                        continue;
                    }
                }

                $processedData[] = [
                    'pic_name' => $visit->pic_name,
                    'role' => $role,
                    'total_store_assigned' => $visit->total_store_assigned,
                    'total_visit_store' => $visit->total_visit_store,
                    'status' => $visit->status
                ];
            }

            // Apply pagination to processed data
            $total = count($processedData);
            $offset = ($page - 1) * $perPage;
            $paginatedData = array_slice($processedData, $offset, $perPage);

            return response()->json([
                'status' => 'success',
                'data' => $paginatedData,
                'pagination' => [
                    'current_page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get visit statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get visits by PIC name for detail view
     */
    public function getVisitsByPic(Request $request, $picName)
    {
        try {
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $status = $request->get('status');

            $query = DB::connection('pgsql')
                ->table('visit_lists as vl')
                ->leftJoin('contact_group_customer as cgc', 'vl.customer_id', '=', 'cgc.customer_no')
                ->leftJoin('contact_group_customer as cgc_child', 'vl.customer_child_id', '=', 'cgc_child.customer_no')
                ->select([
                    'vl.*',
                    'cgc.customer_name',
                    'cgc_child.customer_name as customer_child_name'
                ])
                ->where('vl.pic_name', $picName);

            // Apply filters
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('vl.visit_id', 'ILIKE', "%{$search}%")
                      ->orWhere('cgc.customer_name', 'ILIKE', "%{$search}%")
                      ->orWhere('cgc_child.customer_name', 'ILIKE', "%{$search}%");
                });
            }

            if (!empty($dateFrom)) {
                $query->where('vl.date', '>=', $dateFrom);
            }

            if (!empty($dateTo)) {
                $query->where('vl.date', '<=', $dateTo);
            }

            if (!empty($status)) {
                $query->where('vl.status', $status);
            }

            $total = $query->count();
            $visits = $query->orderBy('vl.created_at', 'desc')
                           ->offset(($request->get('page', 1) - 1) * $perPage)
                           ->limit($perPage)
                           ->get();

            return response()->json([
                'status' => 'success',
                'data' => $visits,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $perPage,
                    'current_page' => $request->get('page', 1),
                    'last_page' => ceil($total / $perPage)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get visits by PIC: ' . $e->getMessage()
            ], 500);
        }
    }
}