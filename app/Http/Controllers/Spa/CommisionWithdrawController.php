<?php

namespace App\Http\Controllers\Spa;

use App\Exports\CommisionWithdrawExport;
use App\Http\Controllers\Controller;
use App\Models\CommisionWithdraw;
use App\Models\CommisionWithdrawApproval;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CommisionWithdrawController extends Controller
{
    public function index($commision_withdraw_id = null)
    {
        return view('spa.spa-index');
    }

    public function listCommisionWithdraw(Request $request)
    {
        $search = $request->search;
        $status = $request->status;
        $role_id = $request->role_id;
        $request_by = $request->request_by;
        $created_at = $request->created_at;

        $commision_wd =  CommisionWithdraw::query();
        if ($search) {
            $commision_wd->where(function ($query) use ($search) {
                $query->where('nama_rekening', 'like', "%$search%");
                $query->orWhere('nama_bank', 'like', "%$search%");
                $query->orWhere('phone', 'like', "%$search%");
                $query->orWhere('email', 'like', "%$search%");
                $query->orWhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
                $query->orWhereHas('requestBy', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            });
        }

        if ($status) {
            $commision_wd->whereIn('status', $status);
        }

        if ($role_id) {
            $commision_wd->where('role_id', $role_id);
        }

        if ($request_by) {
            $commision_wd->where('request_by', $request_by);
        }


        $role = auth()->user()->role;
        if ($role->role_type == 'ahligizi') {
            $commision_wd->where('role_id', $role->id);
        }

        if ($created_at) {
            $commision_wd->whereBetween('created_at', $created_at);
        }

        $commision_wds = $commision_wd->orderBy('created_at', 'desc')->paginate($request->perpage);



        $commision = Transaction::whereHas('userCreated', function ($query) {
            if (auth()->user()->role->role_type == 'ahligizi') {
                return $query->whereHas('roles', function ($query) {
                    $query->where('role_type', 'ahligizi');
                });
            }

            return $query;
        });

        return response()->json([
            'status' => 'success',
            'data' => $commision_wds,
            'commisions' => [
                [
                    'label' => 'Total Bagi Hasil (Rp)',
                    'value' => 'Rp ' . number_format($commision->where('status_delivery', 4)->where('status', 7)->sum('commission'), 0, ',', '.')
                ],
                [
                    'label' => 'Jumlah Bagi Hasil yang sudah ditarik',
                    'value' => CommisionWithdraw::where('status', 'success')->sum('amount')
                ],
                [
                    'label' => 'Jumlah Pengajuan Bagi Hasil yang sedang diproses',
                    'value' => $commision->whereIn('status', [3, 7])->whereIn('status_delivery', [1, 21, 3])->sum('commission')
                ],
            ],
            'message' => 'List Commision Withdraw'
        ]);
    }


    public function getDetailCommisionWithdraw($commision_withdraw_id)
    {
        $commision_wd = CommisionWithdraw::with(['commisionWithdrawApprovals'])->find($commision_withdraw_id);

        return response()->json([
            'status' => 'success',
            'data' => $commision_wd,
            'message' => 'Detail Commision Withdraw'
        ]);
    }

    public function saveCommisionWithdraw(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = [
                'user_id'  => $request->user_id,
                'request_by'  => $request->request_by,
                'role_id'  => $request->role_id,
                'email'  => $request->email,
                'phone'  => $request->phone,
                'amount'  => $request->amount,
                'status'  => $request->status,
                'nama_rekening'  => $request->nama_rekening,
                'nomor_rekening'  => $request->nomor_rekening,
                'nama_bank'  => $request->nama_bank,
                'notes'  => $request->notes,
            ];

            CommisionWithdraw::create($data);

            if ($request->status == 'waiting-approval') {
                createNotification(
                    'WD200',
                    [],
                    [
                        'admin' => auth()->user()->name,
                    ],
                    ['brand_id' => 1]
                );
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Commision Withdraw Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Commision Withdraw Gagal Disimpan'
            ], 400);
        }
    }

    public function updateCommisionWithdraw(Request $request, $commision_withdraw_id)
    {
        try {
            DB::beginTransaction();
            $data = [
                'user_id'  => $request->user_id,
                'request_by'  => $request->request_by,
                'role_id'  => $request->role_id,
                'email'  => $request->email,
                'phone'  => $request->phone,
                'amount'  => $request->amount,
                'status'  => $request->status,
                'nama_rekening'  => $request->nama_rekening,
                'nomor_rekening'  => $request->nomor_rekening,
                'nama_bank'  => $request->nama_bank,
                'notes'  => $request->notes,
            ];
            $row = CommisionWithdraw::find($commision_withdraw_id);
            $row->update($data);

            if ($request->status == 'waiting-approval') {
                createNotification(
                    'WD200',
                    [],
                    [
                        'admin' => auth()->user()->name,
                    ],
                    ['brand_id' => 1]
                );
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Commision Withdraw Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Commision Withdraw Gagal Disimpan'
            ], 400);
        }
    }

    public function cancelCommisionWithdraw(Request $request, $commision_withdraw_id)
    {
        $commision = CommisionWithdraw::find($commision_withdraw_id);
        if ($commision) {
            $commision->update([
                'status' => $request->status
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Data Commision Withdraw berhasil di batalkan'
            ]);
        }
    }

    public function approveCommisionWithdraw(Request $request, $commision_withdraw_id)
    {
        $commision = CommisionWithdraw::find($commision_withdraw_id);
        if ($commision) {
            try {
                DB::beginTransaction();
                $commision->update([
                    'status' => 'onprocess'
                ]);

                CommisionWithdrawApproval::create([
                    'commision_withdraw_id' => $commision_withdraw_id,
                    'approved_by' => auth()->user()->id,
                    'status' => 1,
                    'notes' => null
                ]);

                createNotification(
                    'WDA200',
                    [
                        'user_id' => $commision->user_id,
                    ],
                    [
                        'user' => $commision->user_name,
                        'admin' => auth()->user()->name,
                    ],
                    ['brand_id' => 1]
                );
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Commision Withdraw berhasil di approve'
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Commision Withdraw gagal di approve',
                    'error' => $th->getMessage()
                ], 400);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data Commision Withdraw tidak ditemukan'
        ], 400);
    }

    public function rejectCommisionWithdraw(Request $request, $commision_withdraw_id)
    {
        $commision = CommisionWithdraw::find($commision_withdraw_id);
        if ($commision) {
            try {
                DB::beginTransaction();
                $commision->update([
                    'status' => 'rejected',
                ]);

                CommisionWithdrawApproval::create([
                    'commision_withdraw_id' => $commision_withdraw_id,
                    'approved_by' => auth()->user()->id,
                    'status' => 2,
                    'notes' => $request->note
                ]);

                createNotification(
                    'WDR200',
                    [
                        'user_id' => $commision->user_id,
                    ],
                    [
                        'user' => $commision->user_name,
                        'admin' => auth()->user()->name,
                        'alasan' => $request->note ?? '-'
                    ],
                    ['brand_id' => 1]
                );
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Data Commision Withdraw berhasil di reject'
                ]);
            } catch (\Throwable $th) {
                DB::rollback();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data Commision Withdraw gagal di reject',
                    'error' => $th->getMessage()
                ], 400);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data Commision Withdraw tidak ditemukan'
        ], 400);
    }

    public function export()
    {
        $file_name = 'Withdraw-' . date('d-m-Y') . '.xlsx';

        Excel::store(new CommisionWithdrawExport(null), $file_name, 's3', null, [
            'visibility' => 'public',
        ]);
        return response()->json([
            'status' => 'success',
            'data' => Storage::disk('s3')->url($file_name),
            'message' => 'List Withdraw'
        ]);
    }
}
