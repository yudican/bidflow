<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\CategoryCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class CategoryCaseController extends Controller
{
    public function index($category_case_id = null)
    {
        return view('spa.spa-index');
    }

    public function listCategoryCase(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $category_case =  CategoryCase::query();
        if ($search) {
            $category_case->where(function ($query) use ($search) {
                $query->where('category_name', 'like', "%$search%");
            });
        }

        if ($status) {
            $category_case->whereIn('status', $status);
        }


        $category_cases = $category_case->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $category_cases,
            'message' => 'List CategoryCase'
        ]);
    }


    public function getDetailCategoryCase($category_case_id)
    {
        $brand = CategoryCase::find($category_case_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail CategoryCase'
        ]);
    }

    public function saveCategoryCase(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'type_id'  => $request->type_id,
                'category_name'  => $request->category_name,
                'notes'  => $request->notes,
            ];

            CategoryCase::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Category Type Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data CategoryCase Gagal Disimpan'
            ], 400);
        }
    }

    public function updateCategoryCase(Request $request, $category_case_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'type_id'  => $request->type_id,
                'category_name'  => $request->category_name,
                'notes'  => $request->notes,
            ];
            $row = CategoryCase::find($category_case_id);
            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Category Type Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data CategoryCase Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteCategoryCase($category_case_id)
    {
        $banner = CategoryCase::find($category_case_id);
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data CategoryCase berhasil dihapus'
        ]);
    }
}
