<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\SourceCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Str;

class SourceCaseController extends Controller
{
    public function index($source_case_id = null)
    {
        return view('spa.spa-index');
    }

    public function listSourceCase(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $source_case =  SourceCase::query();
        if ($search) {
            $source_case->where(function ($query) use ($search) {
                $query->where('source_name', 'like', "%$search%");
            });
        }

        if ($status) {
            $source_case->whereIn('status', $status);
        }


        $source_cases = $source_case->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $source_cases,
            'message' => 'List SourceCase'
        ]);
    }


    public function getDetailSourceCase($source_case_id)
    {
        $brand = SourceCase::find($source_case_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail SourceCase'
        ]);
    }

    public function saveSourceCase(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'source_name'  => $request->source_name,
                'notes'  => $request->notes,
                'type'  => $request->type,
            ];

            SourceCase::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Source Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data SourceCase Gagal Disimpan'
            ], 400);
        }
    }

    public function updateSourceCase(Request $request, $source_case_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'source_name'  => $request->source_name,
                'notes'  => $request->notes,
                'type'  => $request->type,
            ];
            $row = SourceCase::find($source_case_id);
            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Source Case berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data SourceCase Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteSourceCase($source_case_id)
    {
        $banner = SourceCase::find($source_case_id);
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data SourceCase berhasil dihapus'
        ]);
    }
}
