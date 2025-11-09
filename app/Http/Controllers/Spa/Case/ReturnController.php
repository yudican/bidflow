<?php

namespace App\Http\Controllers\Spa\Case;

use App\Http\Controllers\Controller;
use App\Models\ReturMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index($uid_return = null)
    {
        return view('spa.spa-index');
    }

    public function getListReturn(Request $request)
    {
        $search = $request->search;
        $created_at = $request->date;
        $account_id = $request->account_id;
        $return =  ReturMaster::with(['returRekenings', 'returItems', 'returItems.product', 'returResis']);
        if ($search) {
            $return->where('name', 'like', "%$search%");
            $return->orWhere('email', 'like', "%$search%");
        }

        if ($created_at) {
            $return->whereBetween('created_at', $created_at);
        }

        // cek switch account
        if ($account_id) {
            if($account_id != 'null'){
                $return->where('company_id', $account_id);
            }
        }

        $returns =  $return->orderBy('retur_masters.created_at', 'desc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $returns
        ]);
    }

    public function getReturnDetail($uid_return)
    {
        $return = ReturMaster::with(['returRekenings', 'returItems', 'returItems.product', 'returResis'])->where('uid_retur', $uid_return)->first();

        return response()->json([
            'status' => 'success',
            'data' => $return
        ]);
    }

    public function reject(Request $request)
    {
        $return = ReturMaster::where('uid_retur', $request->uid_retur)->first();

        if ($return) {
            $return->status = 2;
            $return->save();

            sendEmailSingle(
                'RET400',
                [
                    'email' => $return->email,
                ],
                [
                    'user' => $return->name,
                ],
                ['brand_id' => 1],
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Return berhasil di reject',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Return tidak ditemukan',
        ], 400);
    }
    public function approve(Request $request)
    {
        $return = ReturMaster::where('uid_retur', $request->uid_retur)->first();

        if ($return) {
            try {
                DB::beginTransaction();
                $return->status = 1;
                $return->save();

                sendEmailSingle(
                    'RET200',
                    [
                        'email' => $return->email,
                    ],
                    [
                        'user' =>  $return->name,
                        'actionTitle' => 'Input Resi',
                        'actionUrl' => 'https://case.flimty.co/return/resi/' . $request->uid_retur,
                    ],
                    ['brand_id' => 1],
                    'forgot-password'
                );
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Retuen berhasil di approve',
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Retuen tidak ditemukan',
                ], 400);
            }
        }


        return response()->json([
            'status' => 'success',
            'message' => 'Refund tidak ditemukan',
        ], 400);
    }
}
