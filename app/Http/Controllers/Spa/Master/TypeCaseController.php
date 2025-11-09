<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\TypeCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class TypeCaseController extends Controller
{
    public function index($type_case_id = null)
    {
        return view('spa.spa-index');
    }

    public function listTypeCase(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $type_case =  TypeCase::query();
        if ($search) {
            $type_case->where(function ($query) use ($search) {
                $query->where('type_name', 'like', "%$search%");
                $query->orWhere('code', 'like', "%$search%");
                $query->orWhere('notes', 'like', "%$search%");
            });
        }

        if ($status) {
            $type_case->whereIn('status', $status);
        }


        $type_cases = $type_case->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $type_cases,
            'message' => 'List TypeCase'
        ]);
    }


    public function getDetailTypeCase($type_case_id)
    {
        $brand = TypeCase::find($type_case_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail TypeCase'
        ]);
    }

    public function saveTypeCase(Request $request)
    {
        $request->validate([
            'type_name' => 'required|string|max:255',
            'code' => 'required|unique:type_cases,code',
        ], [
            'code.unique' => 'Maaf, Kode yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.',
        ]);

        try {
            DB::beginTransaction();
            $data = [
                'type_name'  => $request->type_name,
                'code'  => $request->code,
                'notes'  => $request->notes,
            ];

            TypeCase::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Type Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data TypeCase Gagal Disimpan'
            ], 400);
        }
    }

    public function updateTypeCase(Request $request, $type_case_id)
    {
        $request->validate([
            'type_name' => 'required|string|max:255',
            'code' => 'required|unique:type_cases,code,' . $type_case_id
        ], [
            'code.unique' => 'Maaf, Kode yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.',
        ]);
        
        try {
            DB::beginTransaction();
            $data = [
                'type_name'  => $request->type_name,
                'code'  => $request->code,
                'notes'  => $request->notes,
            ];
            $row = TypeCase::find($type_case_id);
            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Type Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data TypeCase Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteTypeCase($type_case_id)
    {
        $banner = TypeCase::find($type_case_id);
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data TypeCase berhasil dihapus'
        ]);
    }
}
