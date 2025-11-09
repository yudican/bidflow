<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class CategoryController extends Controller
{
    public function index($category_id = null)
    {
        return view('spa.spa-index');
    }

    public function listCategory(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $row =  Category::query();
        if ($search) {
            $row->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status) {
            $row->whereIn('status', $status);
        }


        $rows = $row->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'message' => 'List Category'
        ]);
    }


    public function getDetailCategory($category_id)
    {
        $row = Category::find($category_id);

        return response()->json([
            'status' => 'success',
            'data' => $row,
            'message' => 'Detail Category'
        ]);
    }

    public function saveCategory(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'slug'  => Str::slug($request->name),
                'status'  => $request->status
            ];

            Category::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Category Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Category Gagal Disimpan'
            ], 400);
        }
    }

    public function updateCategory(Request $request, $category_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'slug'  => Str::slug($request->name),
                'status'  => $request->status
            ];
            $row = Category::find($category_id);

            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Category Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Category Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteCategory($category_id)
    {
        $row = Category::find($category_id);
        $row->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data Category berhasil dihapus'
        ]);
    }
}
