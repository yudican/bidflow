<?php

namespace App\Http\Controllers\Spa\Case;

use App\Http\Controllers\Controller;
use App\Models\RefundMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
  public function index($uid_refund = null)
  {
    return view('spa.spa-index');
  }

  public function getListRefund(Request $request)
  {
    $search = $request->search;
    $created_at = $request->date;
    $account_id = $request->account_id;
    $refund =  RefundMaster::with(['refundRekenings', 'refundItems', 'refundItems.product', 'refundResis']);
    if ($search) {
      $refund->where('name', 'like', "%$search%");
      $refund->orWhere('email', 'like', "%$search%");
    }

    if ($created_at) {
      $refund->whereBetween('created_at', $created_at);
    }

    // cek switch account
    if ($account_id) {
      if ($account_id != 'null') {
        $refund->where('company_id', $account_id);
      }
    }

    $refunds =  $refund->orderBy('refund_masters.created_at', 'desc')->paginate($request->perpage);

    return response()->json([
      'status' => 'success',
      'data' => $refunds
    ]);
  }

  public function getRefundnDetail($uid_refund)
  {
    $refund = RefundMaster::with(['refundRekenings', 'refundItems', 'refundItems.product', 'refundResis'])->where('uid_refund', $uid_refund)->first();

    return response()->json([
      'status' => 'success',
      'data' => $refund
    ]);
  }

  public function reject(Request $request)
  {
    $refund = RefundMaster::where('uid_refund', $request->uid_refund)->first();

    if ($refund) {
      $refund->status = 2;
      $refund->save();

      sendEmailSingle(
        'REF400',
        [
          'email' => $refund->email,
        ],
        [
          'user' => $refund->name,
        ],
        ['brand_id' => 1],
      );

      return response()->json([
        'status' => 'success',
        'message' => 'Refund berhasil di reject',
      ]);
    }


    return response()->json([
      'status' => 'success',
      'message' => 'Refund tidak ditemukan',
    ], 400);
  }

  public function approve(Request $request)
  {
    $refund = RefundMaster::where('uid_refund', $request->uid_refund)->first();

    if ($refund) {
      try {
        DB::beginTransaction();
        $refund->status = 1;
        $refund->save();

        sendEmailSingle(
          'REF200',
          [
            'email' => $refund->email,
          ],
          [
            'user' =>  $refund->name,
            'actionTitle' => 'Input Resi',
            'actionUrl' => 'https://case.flimty.co/refund/resi/' . $request->uid_refund,
          ],
          ['brand_id' => 1],
          'forgot-password'
        );
        DB::commit();
        return response()->json([
          'status' => 'success',
          'message' => 'Refund berhasil di approve',
        ]);
      } catch (\Throwable $th) {
        DB::rollBack();
        return response()->json([
          'status' => 'success',
          'message' => 'Refund tidak ditemukan',
        ], 400);
      }
    }


    return response()->json([
      'status' => 'success',
      'message' => 'Refund tidak ditemukan',
    ], 400);
  }
}
