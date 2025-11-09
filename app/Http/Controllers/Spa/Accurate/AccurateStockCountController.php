<?php

namespace App\Http\Controllers\Spa\Accurate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * AccurateStockCountController
 * 
 * Controller untuk mengelola stock count dengan fitur:
 * - Save Draft: Menyimpan data dengan status 'draft'
 * - Save Submit: Menyimpan data dengan status 'submitted'
 * - Submit Draft: Mengubah status dari 'draft' ke 'submitted'
 * - Revert to Draft: Mengubah status dari 'submitted' ke 'draft'
 * - Filter berdasarkan status
 * - Statistik berdasarkan status
 * 
 * Status yang tersedia:
 * - draft: Data masih dalam tahap draft, bisa diedit
 * - submitted: Data sudah disubmit, final
 */
class AccurateStockCountController extends Controller
{
  /**
   * Handle file uploads and return comma-separated filenames
   */
  private function handleFileUploads($files)
  {
    $uploadedFiles = [];

    foreach ($files as $file) {
      if ($file->isValid()) {
        // Generate unique filename
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        // Store file in S3 bucket
        $path = Storage::disk('s3')->put('upload/stock_count_attachments', $file, 'public');

        if ($path) {
          $uploadedFiles[] = Storage::disk('s3')->url($path);
        }
      }
    }

    return implode(',', $uploadedFiles);
  }

  /**
   * Delete uploaded files
   */
  private function deleteAttachments($attachments)
  {
    if (!empty($attachments)) {
      $files = explode(',', $attachments);
      foreach ($files as $file) {
        $filePath = 'upload/stock_count_attachments/' . trim($file);
        if (Storage::disk('s3')->exists($filePath)) {
          Storage::disk('s3')->delete($filePath);
        }
      }
    }
  }

  /**
   * Create activity log
   */
  private function createActivity($activityType, $title, $description, $userName, $referenceId, $referenceType, $metadata = null)
  {
    try {
      DB::connection('pgsql')->table('activities')->insert([
        'activity_type' => $activityType,
        'title' => $title,
        'description' => $description,
        'user_name' => $userName,
        'user_id' => null, // You can add user ID if available
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
   * Get stock counts with filter and pagination
   */
  public function getStockCounts(Request $request)
  {
    $perPage = $request->input('per_page', 10);
    $search = $request->input('search');
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    $customerId = $request->input('customer_id');
    $customerChildId = $request->input('customer_child_id');
    $createdBy = $request->input('created_by');
    $status = $request->input('status');

    // dd($perPage.' -- '.$search.' -- '.$dateFrom.' -- '.$dateTo.' -- '.$customerId.' -- '.$customerChildId.' -- '.$createdBy.' -- '.$status.' -- '.)

    $query = DB::connection('pgsql')
      ->table('stock_count_lists as scl')
      ->leftJoin('accurate_customers as ac', 'scl.customer_id', '=', 'ac.customer_no')
      ->leftJoin('contact_group_customer as cc', 'scl.customer_child_id', '=', 'cc.customer_no')
      ->leftJoin('stock_count_items as sci', 'scl.id', '=', 'sci.stock_count_list_id')
      ->select(
        'scl.count_id',
        'scl.date',
        'scl.customer_id',
        'scl.customer_child_id',
        'cc.customer_name as customer_child_name',
        'ac.name as customer_name',
        'scl.pic_name',
        'scl.created_by',
        'scl.status',
        'scl.notes',
        'scl.attachment_reguler',
        'scl.attachment_expired',
        'scl.attachment_gimmick',
        'scl.attachment_0',
        DB::raw('COUNT(sci.id) as total_items'),
        DB::raw('COUNT(CASE WHEN sci.stock_type = \'reguler\' THEN 1 END) as count_reguler'),
        DB::raw('COUNT(CASE WHEN sci.stock_type = \'expired\' THEN 1 END) as count_expired'),
        DB::raw('COUNT(CASE WHEN sci.stock_type = \'gimmic\' THEN 1 END) as count_gimmick'),
        'scl.created_at',
        'scl.updated_at'
      )
      // ->where('scl.created_by', auth()->user()->name)
      ->groupBy(
        'scl.id',
        'scl.count_id',
        'scl.date',
        'scl.customer_id',
        'scl.customer_child_id',
        'ac.name',
        'cc.customer_name',
        'scl.pic_name',
        'scl.created_by',
        'scl.status',
        'scl.notes',
        'scl.attachment_reguler',
        'scl.attachment_expired',
        'scl.attachment_gimmick',
        'scl.attachment_0',
        'scl.created_at',
        'scl.updated_at'
      );

    // Apply filters
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('scl.count_id', 'ILIKE', "%{$search}%")
          ->orWhere('ac.name', 'ILIKE', "%{$search}%")
          ->orWhere('scl.pic_name', 'ILIKE', "%{$search}%");
      });
    }

    if ($dateFrom) {
      $query->where('scl.date', '>=', $dateFrom);
    }

    if ($dateTo) {
      $query->where('scl.date', '<=', $dateTo);
    }

    if ($customerId) {
      $query->where('scl.customer_id', $customerId);
    }

    if ($customerChildId) {
      $query->where('scl.customer_child_id', $customerChildId);
    }

    if ($createdBy) {
      $query->where('scl.created_by', 'ILIKE', "%{$createdBy}%");
    }

    if ($status) {
      $query->where('scl.status', $status);
    }

    $data = $query->orderBy('scl.created_at', 'desc')
      ->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'data' => $data,
      'message' => 'Stock counts retrieved successfully'
    ]);
  }

  /**
   * Get stock count detail by ID
   */
  public function getStockCountDetail($id)
  {
    // Get stock count list header
    $stockCountList = DB::connection('pgsql')
      ->table('stock_count_lists as scl')
      ->leftJoin('accurate_customers as ac', 'scl.customer_id', '=', 'ac.customer_no')
      ->leftJoin('contact_group_customer as cc', 'scl.customer_child_id', '=', 'cc.customer_no')
      ->select(
        'scl.id',
        'scl.count_id',
        'scl.date',
        'scl.customer_id',
        'scl.customer_child_id',
        'ac.name as customer_name',
        'cc.customer_name as customer_child_name',
        'cc.customer_email as customer_child_email',
        'cc.work_phone as customer_child_phone',
        'ac.email as customer_email',
        'ac.work_phone as customer_phone',
        'scl.pic_name',
        'scl.notes',
        'scl.status',
        'scl.created_by',
        'scl.attachment_reguler',
        'scl.attachment_expired',
        'scl.attachment_gimmick',
        'scl.attachment_0',
        'scl.created_at',
        'scl.updated_at'
      )
      ->where('scl.count_id', $id)
      ->first();

    if (!$stockCountList) {
      return response()->json([
        'status' => 'error',
        'message' => 'Stock count not found'
      ], 404);
    }

    // Get stock count items
    $stockCountItems = DB::connection('pgsql')
      ->table('stock_count_items as sci')
      ->leftJoin('accurate_items as ai', 'sci.product_code', '=', 'ai.item_no')
      ->select(
        'sci.id',
        'sci.product_code',
        'ai.name as product_name',
        'ai.unit1 as product_unit',
        'ai.item_type_name as product_type',
        'sci.actual_stock',
        'sci.stock_type',
        'sci.expired_date',
        'sci.key',
        'sci.created_at',
        'sci.updated_at'
      )
      ->where('sci.stock_count_list_id', $stockCountList->id)
      ->get();

    // Group items by stock type for easier frontend handling
    $itemsByType = [
      'reguler' => $stockCountItems->where('stock_type', 'reguler')->values(),
      'expired' => $stockCountItems->where('stock_type', 'expired')->values(),
      'gimmick' => $stockCountItems->where('stock_type', 'gimmic')->values()
    ];

    // Add counts for each category
    $stockCountList->count_reguler = $itemsByType['reguler']->count();
    $stockCountList->count_expired = $itemsByType['expired']->count();
    $stockCountList->count_gimmick = $itemsByType['gimmick']->count();
    $stockCountList->total_items = $stockCountItems->count();

    return response()->json([
      'status' => 'success',
      'data' => [
        'list' => $stockCountList,
        'items' => $stockCountItems,
        'items_by_type' => $itemsByType
      ],
      'message' => 'Stock count detail retrieved successfully'
    ]);
  }

  /**
   * Create new stock count
   */
  public function createStockCount(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'count_id' => 'required|string|max:255',
      'date' => 'required|date',
      'customer_id' => 'required|string|max:255',
      'customer_child_id' => 'nullable|string|max:255',
      'product_code' => 'required|string|max:255',
      'actual_stock' => 'required|string|max:255',
      'pic_name' => 'nullable|string|max:255',
      'notes' => 'nullable|string',
      'key' => 'nullable|string|max:255',
      'status' => 'nullable|string|in:draft,submitted',
      'created_by' => 'required|string|max:255',
      'attachment_*' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx|max:10240' // Max 10MB per file
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      // Handle file uploads
      $attachments = '';
      $attachmentFiles = [];

      // Check for dynamic attachment fields (attachment_0, attachment_1, etc.)
      foreach ($request->allFiles() as $key => $file) {
        if (strpos($key, 'attachment_') === 0) {
          $attachmentFiles[] = $file;
        }
      }

      if (!empty($attachmentFiles)) {
        $attachments = $this->handleFileUploads($attachmentFiles);
      }

      $stockCountId = DB::connection('pgsql')->table('stock_count')->insertGetId([
        'count_id' => $request->count_id,
        'date' => $request->date,
        'customer_id' => $request->customer_id,
        'customer_child_id' => $request->customer_child_id,
        'product_code' => $request->product_code,
        'actual_stock' => $request->actual_stock,
        'pic_name' => $request->pic_name,
        'notes' => $request->notes,
        'attachments' => $attachments,
        'key' => $request->key,
        'status' => $request->status ?? 'submitted',
        'created_by' => $request->created_by,
        'created_at' => now(),
        'updated_at' => now()
      ]);

      // DB::connection('pgsql')->statement('SELECT refresh_accurate_stock_comparison()');

      // Create activity log
      $this->createActivity(
        'stock_count',
        'Input data Stock Count [' . $request->count_id . ']',
        'User melakukan input data stock count baru',
        $request->created_by ?? '[User Input]',
        $stockCountId,
        'stock_count',
        [
          'count_id' => $request->count_id,
          'customer_id' => $request->customer_id,
          'product_code' => $request->product_code,
          'actual_stock' => $request->actual_stock,
          'action' => 'create'
        ]
      );

      return response()->json([
        'status' => 'success',
        'data' => ['id' => $stockCountId],
        'message' => 'Stock count created successfully'
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create stock count: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Save stock count as draft
   */
  public function saveDraft(Request $request)
  {
    $request->merge(['status' => 'draft']);
    return $this->createStockCount($request);
  }

  /**
   * Save stock count as submitted
   */
  public function saveSubmit(Request $request)
  {
    $request->merge(['status' => 'submitted']);
    return $this->createStockCount($request);
  }

  /**
   * Submit draft stock count (change status from draft to submitted)
   */
  public function submitDraft($id)
  {
    try {
      // Check if stock count exists and is draft
      $stockCount = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->first();

      if (!$stockCount) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      if ($stockCount->status !== 'draft') {
        return response()->json([
          'status' => 'error',
          'message' => 'Only draft stock counts can be submitted'
        ], 400);
      }

      DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->update([
          'status' => 'submitted',
          'updated_at' => now()
        ]);

      // Create activity log
      $this->createActivity(
        'stock_count',
        'Submit Stock Count [' . $stockCount->count_id . ']',
        'User mengubah status stock count dari draft ke submitted',
        $stockCount->created_by ?? '[User Input]',
        $id,
        'stock_count',
        [
          'count_id' => $stockCount->count_id,
          'old_status' => 'draft',
          'new_status' => 'submitted',
          'action' => 'submit_draft'
        ]
      );

      return response()->json([
        'status' => 'success',
        'message' => 'Stock count submitted successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to submit stock count: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Revert submitted stock count to draft
   */
  public function revertToDraft($id)
  {
    try {
      // Check if stock count exists and is submitted
      $stockCount = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->first();

      if (!$stockCount) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      if ($stockCount->status !== 'submitted') {
        return response()->json([
          'status' => 'error',
          'message' => 'Only submitted stock counts can be reverted to draft'
        ], 400);
      }

      DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->update([
          'status' => 'draft',
          'updated_at' => now()
        ]);

      // Create activity log
      $this->createActivity(
        'stock_count',
        'Revert Stock Count to Draft [' . $stockCount->count_id . ']',
        'User mengubah status stock count dari submitted ke draft',
        $stockCount->created_by ?? '[User Input]',
        $id,
        'stock_count',
        [
          'count_id' => $stockCount->count_id,
          'old_status' => 'submitted',
          'new_status' => 'draft',
          'action' => 'revert_to_draft'
        ]
      );

      return response()->json([
        'status' => 'success',
        'message' => 'Stock count reverted to draft successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to revert stock count: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Update stock count
   */
  public function updateStockCount(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'count_id' => 'sometimes|required|string|max:255',
      'date' => 'sometimes|required|date',
      'customer_id' => 'sometimes|required|string|max:255',
      'customer_child_id' => 'nullable|string|max:255',
      'product_code' => 'sometimes|required|string|max:255',
      'actual_stock' => 'sometimes|required|string|max:255',
      'pic_name' => 'nullable|string|max:255',
      'notes' => 'nullable|string',
      'key' => 'nullable|string|max:255',
      'status' => 'nullable|string|in:draft,submitted',
      'created_by' => 'sometimes|required|string|max:255',
      'attachment_*' => 'nullable|file|mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx|max:10240' // Max 10MB per file
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      // Check if stock count exists
      $exists = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->exists();

      if (!$exists) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      // Get current record to check for existing attachments
      $currentRecord = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->first();

      $updateData = array_filter($request->only([
        'count_id',
        'date',
        'customer_id',
        'customer_child_id',
        'product_code',
        'actual_stock',
        'pic_name',
        'notes',
        'key',
        'status',
        'created_by'
      ]));

      // Handle file uploads
      $attachmentFiles = [];

      // Check for dynamic attachment fields (attachment_0, attachment_1, etc.)
      foreach ($request->allFiles() as $key => $file) {
        if (strpos($key, 'attachment_') === 0) {
          $attachmentFiles[] = $file;
        }
      }

      if (!empty($attachmentFiles)) {
        // Delete old attachments if new files are uploaded
        if ($currentRecord && $currentRecord->attachments) {
          $this->deleteAttachments($currentRecord->attachments);
        }

        $updateData['attachments'] = $this->handleFileUploads($attachmentFiles);
      }

      $updateData['updated_at'] = now();

      DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->update($updateData);

      // Create activity log
      $this->createActivity(
        'stock_count',
        'Update data Stock Count [' . ($request->count_id ?? $currentRecord->count_id) . ']',
        'User melakukan update data stock count',
        $request->created_by ?? $currentRecord->created_by ?? '[User Input]',
        $id,
        'stock_count',
        [
          'count_id' => $request->count_id ?? $currentRecord->count_id,
          'customer_id' => $request->customer_id ?? $currentRecord->customer_id,
          'product_code' => $request->product_code ?? $currentRecord->product_code,
          'action' => 'update'
        ]
      );

      return response()->json([
        'status' => 'success',
        'message' => 'Stock count updated successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to update stock count: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Delete stock count
   */
  public function deleteStockCount($id)
  {
    try {
      // Get record to delete attachments
      $record = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->first();

      if (!$record) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      // Delete attachments if exist
      if ($record->attachments) {
        $this->deleteAttachments($record->attachments);
      }

      $deleted = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->delete();

      if (!$deleted) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      // Create activity log
      $this->createActivity(
        'stock_count',
        'Delete data Stock Count [' . $record->count_id . ']',
        'User menghapus data stock count',
        $record->created_by ?? '[User Input]',
        $id,
        'stock_count',
        [
          'count_id' => $record->count_id,
          'customer_id' => $record->customer_id,
          'product_code' => $record->product_code,
          'action' => 'delete'
        ]
      );

      return response()->json([
        'status' => 'success',
        'message' => 'Stock count deleted successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to delete stock count: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get stock count statistics by status
   */
  public function getStockCountStatistics(Request $request)
  {
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    $customerId = $request->input('customer_id');
    $customerChildId = $request->input('customer_child_id');

    $query = DB::connection('pgsql')
      ->table('stock_count as sc')
      ->select(
        'sc.status',
        DB::raw('COUNT(*) as total_count'),
        DB::raw('COUNT(DISTINCT sc.count_id) as unique_count_ids'),
        DB::raw('COUNT(DISTINCT sc.customer_id) as unique_customers')
      )
      ->groupBy('sc.status');

    if ($dateFrom) {
      $query->where('sc.date', '>=', $dateFrom);
    }

    if ($dateTo) {
      $query->where('sc.date', '<=', $dateTo);
    }

    if ($customerId) {
      $query->where('sc.customer_id', $customerId);
    }

    if ($customerChildId) {
      $query->where('sc.customer_child_id', $customerChildId);
    }

    $data = $query->get();

    // Calculate totals
    $totals = [
      'total_records' => $data->sum('total_count'),
      'total_count_ids' => $data->sum('unique_count_ids'),
      'total_customers' => $data->sum('unique_customers'),
      'draft_count' => $data->where('status', 'draft')->first()->total_count ?? 0,
      'submitted_count' => $data->where('status', 'submitted')->first()->total_count ?? 0
    ];

    return response()->json([
      'status' => 'success',
      'data' => [
        'statistics' => $data,
        'totals' => $totals
      ],
      'message' => 'Stock count statistics retrieved successfully'
    ]);
  }

  /**
   * Get stock count summary by customer
   */
  public function getStockCountSummary(Request $request)
  {
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    $customerId = $request->input('customer_id');
    $customerChildId = $request->input('customer_child_id');

    $query = DB::connection('pgsql')
      ->table('stock_count as sc')
      ->leftJoin('accurate_customers as ac', 'sc.customer_id', '=', 'ac.customer_no')
      ->select(
        'sc.customer_id',
        'sc.customer_child_id',
        'ac.name as customer_name',
        DB::raw('COUNT(*) as total_counts'),
        DB::raw('COUNT(DISTINCT sc.product_code) as unique_products'),
        DB::raw('SUM(CAST(sc.actual_stock AS NUMERIC)) as total_stock'),
        DB::raw('MIN(sc.date) as first_count_date'),
        DB::raw('MAX(sc.date) as last_count_date')
      )
      ->groupBy('sc.customer_id', 'sc.customer_child_id', 'ac.name');

    if ($dateFrom) {
      $query->where('sc.date', '>=', $dateFrom);
    }

    if ($dateTo) {
      $query->where('sc.date', '<=', $dateTo);
    }

    if ($customerId) {
      $query->where('sc.customer_id', $customerId);
    }

    if ($customerChildId) {
      $query->where('sc.customer_child_id', $customerChildId);
    }

    $data = $query->orderBy('total_counts', 'desc')->get();

    return response()->json([
      'status' => 'success',
      'data' => $data,
      'message' => 'Stock count summary retrieved successfully'
    ]);
  }

  /**
   * Get stock count by count_id
   */
  public function getStockCountByCountId($countId)
  {
    $stockCounts = DB::connection('pgsql')
      ->table('stock_count as sc')
      ->leftJoin('accurate_customers as ac', 'sc.customer_id', '=', 'ac.customer_no')
      ->leftJoin('accurate_items as ai', 'sc.product_code', '=', 'ai.item_no')
      ->select(
        'sc.id',
        'sc.count_id',
        'sc.date',
        'sc.customer_id',
        'sc.customer_child_id',
        'ac.name as customer_name',
        'sc.product_code',
        'ai.name as product_name',
        'sc.actual_stock',
        'sc.pic_name',
        'sc.notes',
        'sc.attachments',
        'sc.key',
        'sc.status',
        'sc.created_by',
        'sc.created_at',
        'sc.updated_at'
      )
      ->where('sc.count_id', $countId)
      ->orderBy('sc.product_code')
      ->get();

    if ($stockCounts->isEmpty()) {
      return response()->json([
        'status' => 'error',
        'message' => 'No stock counts found for the given count_id'
      ], 404);
    }

    return response()->json([
      'status' => 'success',
      'data' => $stockCounts,
      'message' => 'Stock counts retrieved successfully'
    ]);
  }

  /**
   * Bulk create stock counts
   */
  public function bulkCreateStockCounts(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'stock_counts' => 'required|array|min:1',
      'stock_counts.*.count_id' => 'required|string|max:255',
      'stock_counts.*.date' => 'required|date',
      'stock_counts.*.customer_id' => 'required|string|max:255',
      'stock_counts.*.customer_child_id' => 'nullable|string|max:255',
      'stock_counts.*.product_code' => 'required|string|max:255',
      'stock_counts.*.actual_stock' => 'required|string|max:255',
      'stock_counts.*.pic_name' => 'nullable|string|max:255',
      'stock_counts.*.notes' => 'nullable|string',
      'stock_counts.*.key' => 'nullable|string|max:255',
      'stock_counts.*.status' => 'nullable|string|in:draft,submitted',
      'stock_counts.*.created_by' => 'required|string|max:255',
      'stock_counts.*.attachments' => 'nullable|string' // For bulk, attachments should be comma-separated string
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      $insertData = [];
      $now = now();

      foreach ($request->stock_counts as $stockCount) {
        $insertData[] = [
          'count_id' => $stockCount['count_id'],
          'date' => $stockCount['date'],
          'customer_id' => $stockCount['customer_id'],
          'customer_child_id' => $stockCount['customer_child_id'] ?? null,
          'product_code' => $stockCount['product_code'],
          'actual_stock' => $stockCount['actual_stock'],
          'pic_name' => $stockCount['pic_name'] ?? null,
          'notes' => $stockCount['notes'] ?? null,
          'attachments' => $stockCount['attachments'] ?? null,
          'key' => $stockCount['key'] ?? null,
          'status' => $stockCount['status'] ?? 'submitted',
          'created_by' => $stockCount['created_by'],
          'created_at' => $now,
          'updated_at' => $now
        ];
      }

      DB::connection('pgsql')->table('stock_count')->insert($insertData);

      // Create activity log for bulk operation
      $firstStockCount = $request->stock_counts[0];
      $this->createActivity(
        'stock_count',
        'Bulk input data Stock Count [' . count($insertData) . ' records]',
        'User melakukan bulk input data stock count sebanyak ' . count($insertData) . ' records',
        $firstStockCount['created_by'] ?? '[User Input]',
        'bulk_' . time(),
        'stock_count_bulk',
        [
          'total_records' => count($insertData),
          'count_ids' => array_unique(array_column($request->stock_counts, 'count_id')),
          'action' => 'bulk_create'
        ]
      );

      return response()->json([
        'status' => 'success',
        'message' => 'Bulk stock counts created successfully',
        'data' => ['inserted_count' => count($insertData)]
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to create bulk stock counts: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Download attachment file
   */
  public function downloadAttachment($id, $filename)
  {
    try {
      // Verify the stock count exists and has this attachment
      $stockCount = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->first();

      if (!$stockCount) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      if (!$stockCount->attachments || !str_contains($stockCount->attachments, $filename)) {
        return response()->json([
          'status' => 'error',
          'message' => 'Attachment not found'
        ], 404);
      }

      $filePath = 'stock_count_attachments/' . $filename;

      if (!Storage::disk('public')->exists($filePath)) {
        return response()->json([
          'status' => 'error',
          'message' => 'File not found'
        ], 404);
      }

      $fullPath = storage_path('app/public/' . $filePath);
      return response()->download($fullPath);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to download file: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get attachment URLs for a stock count
   */
  public function getAttachmentUrls($id)
  {
    try {
      $stockCount = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->first();

      if (!$stockCount) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      $attachmentUrls = [];
      if ($stockCount->attachments) {
        $files = explode(',', $stockCount->attachments);
        foreach ($files as $file) {
          $filename = trim($file);
          if (!empty($filename)) {
            $attachmentUrls[] = [
              'filename' => $filename,
              'url' => asset('storage/stock_count_attachments/' . $filename),
              'download_url' => url("/api/accurate/stock-count/{$id}/attachment/{$filename}")
            ];
          }
        }
      }

      return response()->json([
        'status' => 'success',
        'data' => [
          'stock_count_id' => $id,
          'attachments' => $attachmentUrls
        ],
        'message' => 'Attachment URLs retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to get attachment URLs: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Upload additional attachments to existing stock count
   */
  public function uploadAttachments(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'attachments' => 'required|array|min:1',
      'attachments.*' => 'file|mimes:jpeg,jpg,png,pdf,doc,docx,xls,xlsx|max:10240' // Max 10MB per file
    ]);

    if ($validator->fails()) {
      return response()->json([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $validator->errors()
      ], 422);
    }

    try {
      // Check if stock count exists
      $stockCount = DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->first();

      if (!$stockCount) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      // Upload new files
      $newAttachments = $this->handleFileUploads($request->file('attachments'));

      // Combine with existing attachments
      $existingAttachments = $stockCount->attachments ?: '';
      $allAttachments = array_filter([trim($existingAttachments), $newAttachments]);
      $finalAttachments = implode(',', $allAttachments);

      // Update record
      DB::connection('pgsql')
        ->table('stock_count')
        ->where('id', $id)
        ->update([
          'attachments' => $finalAttachments,
          'updated_at' => now()
        ]);

      return response()->json([
        'status' => 'success',
        'message' => 'Attachments uploaded successfully',
        'data' => [
          'new_attachments' => explode(',', $newAttachments),
          'all_attachments' => explode(',', $finalAttachments)
        ]
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to upload attachments: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get submitted stock counts grouped by count_id
   */
  public function getSubmittedStockCounts(Request $request)
  {
    $perPage = $request->input('per_page', 10);
    $search = $request->input('search');
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    $customerId = $request->input('customer_id');
    $customerChildId = $request->input('customer_child_id');
    $createdBy = $request->input('created_by');

    $query = DB::connection('pgsql')
      ->table('stock_count as sc')
      ->leftJoin('accurate_customers as ac', 'sc.customer_id', '=', 'ac.customer_no')
      ->select(
        'sc.count_id',
        'sc.date',
        'sc.customer_id',
        'sc.customer_child_id',
        'ac.name as customer_name',
        'sc.pic_name',
        'sc.created_by',
        DB::raw('COUNT(*) as total_items'),
        DB::raw('SUM(CAST(sc.actual_stock AS NUMERIC)) as total_stock'),
        DB::raw('MIN(sc.created_at) as created_at'),
        DB::raw('MAX(sc.updated_at) as updated_at'),
        DB::raw('STRING_AGG(DISTINCT sc.product_code, \', \') as product_codes')
      )
      ->groupBy(
        'sc.count_id',
        'sc.date',
        'sc.customer_id',
        'sc.customer_child_id',
        'ac.name',
        'sc.pic_name',
        'sc.created_by'
      );

    // Apply filters
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('sc.count_id', 'ILIKE', "%{$search}%")
          ->orWhere('ac.name', 'ILIKE', "%{$search}%")
          ->orWhere('sc.pic_name', 'ILIKE', "%{$search}%")
          ->orWhere('sc.created_by', 'ILIKE', "%{$search}%");
      });
    }

    if ($dateFrom) {
      $query->where('sc.date', '>=', $dateFrom);
    }

    if ($dateTo) {
      $query->where('sc.date', '<=', $dateTo);
    }

    if ($customerId) {
      $query->where('sc.customer_id', $customerId);
    }

    if ($customerChildId) {
      $query->where('sc.customer_child_id', $customerChildId);
    }

    if ($createdBy) {
      $query->where('sc.created_by', 'ILIKE', "%{$createdBy}%");
    }

    $data = $query->orderBy('created_at', 'desc')
      ->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'data' => $data,
      'message' => 'Submitted stock counts retrieved successfully'
    ]);
  }

  /**
   * Get stock count items detail by count_id
   */
  public function getStockCountItems($countId)
  {
    try {
      // Get basic count information
      $countInfo = DB::connection('pgsql')
        ->table('stock_count as sc')
        ->leftJoin('accurate_customers as ac', 'sc.customer_id', '=', 'ac.customer_no')
        ->select(
          'sc.count_id',
          'sc.date',
          'sc.customer_id',
          'sc.customer_child_id',
          'ac.name as customer_name',
          'ac.email as customer_email',
          'ac.work_phone as customer_phone',
          'sc.pic_name',
          'sc.created_by',
          DB::raw('MIN(sc.created_at) as created_at'),
          DB::raw('MAX(sc.updated_at) as updated_at')
        )
        ->where('sc.count_id', $countId)
        ->groupBy(
          'sc.count_id',
          'sc.date',
          'sc.customer_id',
          'sc.customer_child_id',
          'ac.name',
          'ac.email',
          'ac.work_phone',
          'sc.pic_name',
          'sc.created_by'
        )
        ->first();

      if (!$countInfo) {
        return response()->json([
          'status' => 'error',
          'message' => 'Stock count not found'
        ], 404);
      }

      // Get all items for this count_id
      $items = DB::connection('pgsql')
        ->table('stock_count as sc')
        ->leftJoin('accurate_items as ai', 'sc.product_code', '=', 'ai.item_no')
        ->select(
          'sc.id',
          'sc.product_code',
          'ai.name as product_name',
          'ai.unit1 as product_unit',
          'ai.item_type_name as product_type',
          'sc.actual_stock',
          'sc.notes',
          'sc.attachments',
          'sc.key',
          'sc.created_at',
          'sc.updated_at'
        )
        ->where('sc.count_id', $countId)
        ->orderBy('sc.product_code')
        ->get();

      // Calculate summary
      $summary = [
        'total_items' => $items->count(),
        'total_stock' => $items->sum(function ($item) {
          return is_numeric($item->actual_stock) ? (float)$item->actual_stock : 0;
        }),
        'items_with_attachments' => $items->where('attachments', '!=', null)->where('attachments', '!=', '')->count(),
        'items_with_notes' => $items->where('notes', '!=', null)->where('notes', '!=', '')->count()
      ];

      return response()->json([
        'status' => 'success',
        'data' => [
          'count_info' => $countInfo,
          'summary' => $summary,
          'items' => $items
        ],
        'message' => 'Stock count items retrieved successfully'
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 'error',
        'message' => 'Failed to retrieve stock count items: ' . $e->getMessage()
      ], 500);
    }
  }
}
