<?php

namespace App\Http\Controllers\Spa\Accurate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccurateItemsController extends Controller
{
  /**
   * Get items with name filter and pagination
   */
  public function getItems(Request $request)
  {
    $perPage = $request->input('per_page', 10);
    $search = $request->input('search');
    $page = $request->input('page', 1);

    $query = DB::connection('pgsql')
      ->table('accurate_items as a')
      ->leftJoin('accurate_stocks as s', 'a.item_no', '=', 's.item_no')
      ->select(
        'a.id',
        'a.accurate_id',
        'a.name',
        'a.item_no',
        'a.unit1',
        'a.unit1_name',
        'a.item_type_name',
        'a.item_category',
        's.quantity as stock_quantity',
        's.quantity_in_all_unit',
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.is_active', 1);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('a.name', 'ILIKE', "%{$search}%")
          ->orWhere('a.item_no', 'ILIKE', "%{$search}%")
          ->orWhere('a.item_type_name', 'ILIKE', "%{$search}%")
          ->orWhere('a.item_category', 'ILIKE', "%{$search}%");
      });
    }

    $data = $query->orderBy('a.name', 'asc')
      ->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
      'status' => 'success',
      'data' => $data,
      'message' => 'Items retrieved successfully'
    ]);
  }

  /**
   * Get item detail by ID
   */
  public function getItemDetail($id)
  {
    $item = DB::connection('pgsql')
      ->table('accurate_items as a')
      ->leftJoin('accurate_stocks as s', 'a.item_no', '=', 's.item_no')
      ->select(
        'a.id',
        'a.accurate_id',
        'a.name',
        'a.item_no',
        'a.unit1',
        'a.unit1_name',
        'a.item_type_name',
        'a.item_category',
        'a.item_category_full',
        's.quantity as stock_quantity',
        's.quantity_in_all_unit',
        's.upc_no',
        'a.created_at',
        'a.updated_at'
      )
      ->where('a.id', $id)
      ->first();

    if (!$item) {
      return response()->json([
        'status' => 'error',
        'message' => 'Item not found'
      ], 404);
    }

    // Decode JSON field if exists
    if ($item->item_category_full) {
      $item->item_category_full = json_decode($item->item_category_full, true);
    }

    return response()->json([
      'status' => 'success',
      'data' => $item,
      'message' => 'Item detail retrieved successfully'
    ]);
  }

  /**
   * Get items by category
   */
  public function getItemsByCategory(Request $request)
  {
    $category = $request->input('category');
    $perPage = $request->input('per_page', 10);
    $search = $request->input('search');

    $query = DB::connection('pgsql')
      ->table('accurate_items as a')
      ->leftJoin('accurate_stocks as s', 'a.item_no', '=', 's.item_no')
      ->select(
        'a.id',
        'a.accurate_id',
        'a.name',
        'a.item_no',
        'a.unit1',
        'a.unit1_name',
        'a.item_type_name',
        'a.item_category',
        's.quantity as stock_quantity',
        's.quantity_in_all_unit'
      );

    if ($category) {
      $query->where('a.item_category', 'ILIKE', "%{$category}%");
    }

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('a.name', 'ILIKE', "%{$search}%")
          ->orWhere('a.item_no', 'ILIKE', "%{$search}%");
      });
    }

    $data = $query->orderBy('a.name', 'asc')
      ->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'data' => $data,
      'message' => 'Items by category retrieved successfully'
    ]);
  }

  /**
   * Get item categories
   */
  public function getItemCategories()
  {
    $categories = DB::connection('pgsql')
      ->table('accurate_items')
      ->select('item_category')
      ->whereNotNull('item_category')
      ->where('item_category', '!=', '')
      ->distinct()
      ->orderBy('item_category', 'asc')
      ->pluck('item_category');

    return response()->json([
      'status' => 'success',
      'data' => $categories,
      'message' => 'Item categories retrieved successfully'
    ]);
  }

  /**
   * Get items with stock information
   */
  public function getItemsWithStock(Request $request)
  {
    $perPage = $request->input('per_page', 10);
    $search = $request->input('search');
    $minStock = $request->input('min_stock', 0);

    $query = DB::connection('pgsql')
      ->table('accurate_items as a')
      ->join('accurate_stocks as s', 'a.item_no', '=', 's.item_no')
      ->select(
        'a.id',
        'a.accurate_id',
        'a.name',
        'a.item_no',
        'a.unit1',
        'a.unit1_name',
        'a.item_type_name',
        'a.item_category',
        's.quantity as stock_quantity',
        's.quantity_in_all_unit',
        's.upc_no'
      )
      ->where('s.quantity', '>=', $minStock);

    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('a.name', 'ILIKE', "%{$search}%")
          ->orWhere('a.item_no', 'ILIKE', "%{$search}%");
      });
    }

    $data = $query->orderBy('s.quantity', 'desc')
      ->paginate($perPage);

    return response()->json([
      'status' => 'success',
      'data' => $data,
      'message' => 'Items with stock retrieved successfully'
    ]);
  }
}
