<?php

namespace App\Http\Controllers\Spa\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\MarginBottom;
use App\Models\ProductVariant;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductMarginBottom extends Controller
{
    public function index($product_margin_id = null)
    {
        return view('spa.spa-index');
    }

    public function listMarginBottom(Request $request)
    {
        $search = $request->search;
        $role_id = $request->role_id;

        $row =  MarginBottom::query();
        if ($search) {
            $row->where(function ($query) use ($search) {
                $query->where('basic_price', 'like', "%$search%");
                $query->orWhere('margin', 'like', "%$search%");
                $query->orWhereHas('productVariant', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
                $query->orWhereHas('role', function ($query) use ($search) {
                    $query->where('role_name', 'like', "%$search%");
                });
            });
        }

        if ($role_id) {
            $row->whereIn('role_id', $role_id);
        }


        $rows = $row->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'message' => 'List Margin Bottom'
        ]);
    }

    public function getDetailMarginBottom($product_margin_id = null)
    {
        $row = MarginBottom::with('productVariant')->find($product_margin_id);

        return response()->json([
            'status' => 'success',
            'data' => [
                'margin' => $row,
                'roles' => Role::whereIn('role_type', ['member', 'agent', 'subagent'])->get(),
            ],
            'message' => 'Detail Margin Bottom'
        ]);
    }

    public function saveMarginBottom(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'basic_price'  => $request->basic_price,
                'role_id'  => $request->role_id,
                'margin'  => $request->margin,
                'description'  => $request->description,
                'product_variant_id'  => $request->product_variant_id,
            ];

            MarginBottom::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Margin Bottom Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Margin Bottom Gagal Disimpan'
            ], 400);
        }
    }

    public function updateMarginBottom(Request $request, $product_margin_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'basic_price'  => $request->basic_price,
                'role_id'  => $request->role_id,
                'margin'  => $request->margin,
                'description'  => $request->description,
                'product_variant_id'  => $request->product_variant_id,
            ];
            $row = MarginBottom::find($product_margin_id);
            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Margin Bottom Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Margin Bottom Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteMarginBottom($product_margin_id)
    {
        $row = MarginBottom::find($product_margin_id);
        $row->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data Margin Bottom berhasil dihapus'
        ]);
    }
}
