<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\NotifAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Str;

class NotifController extends Controller
{
    public function index($notif_id = null)
    {
        return view('spa.spa-index');
    }

    public function listNotif(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $row =  NotifAlert::query();
        if ($search) {
            $row->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            });
        }

        if ($status) {
            $row->where('status', $status == 10 ? 0 : $status);
        }


        $rows = $row->orderBy('created', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $rows,
            'message' => 'List Notif Alert'
        ]);
    }


    public function getDetailNotif($notif_id)
    {
        $row = NotifAlert::find($notif_id);

        return response()->json([
            'status' => 'success',
            'data' => $row,
            'message' => 'Detail Notif Alert'
        ]);
    }

    public function saveNotif(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'uid'  => hash('crc32', Carbon::now()->format('U')),
                'status'  => $request->status,
            ];

            NotifAlert::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Notif Alert Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'gagal',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function updateNotif(Request $request, $notif_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'name'  => $request->name,
                'status'  => $request->status
            ];
            $row = NotifAlert::find($notif_id);

            $row->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Notif Alert Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Notif Alert Gagal Disimpan'
            ], 400);
        }
    }

    public function deleteNotif($notif_id)
    {
        $row = NotifAlert::find($notif_id);
        $row->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data Notif Alert berhasil dihapus'
        ]);
    }
}
