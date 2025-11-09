<?php

namespace App\Http\Controllers\Spa\Case;

use App\Http\Controllers\Controller;
use App\Models\CaseItem;
use App\Models\Cases;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualController extends Controller
{
  public function index($uid_case = null)
  {
    return view('spa.spa-index');
  }

  public function getListManual(Request $request)
  {
    $search = $request->search;
    $created_at = $request->date;
    $account_id = $request->account_id;
    $manual =  Cases::query();
    if ($search) {
      $manual->where('title', 'like', "%$search%");
    }

    if ($created_at) {
      $manual->whereBetween('created_at', $created_at);
    }

    // cek switch account
    // if ($account_id) {
    //   $manual->where('company_id', $account_id);
    // }

    $manuals =  $manual->orderBy('created_at', 'desc')->paginate($request->perpage);

    return response()->json([
      'status' => 'success',
      'data' => $manuals
    ]);
  }

  public function getManualDetail($uid_case)
  {
    $refund = Cases::with(['items'])->where('uid_case', $uid_case)->first();

    return response()->json([
      'status' => 'success',
      'data' => $refund
    ]);
  }

  public function createCase(Request $request)
  {
    try {
      DB::beginTransaction();
      $uid_case = hash('crc32', Carbon::now()->format('U'));
      $data = [
        'title'  => $this->generateTitle(),
        'uid_case' => $uid_case,
        'contact'  => $request->contact,
        'type_id'  => $request->type_id,
        'category_id'  => $request->category_id,
        'priority_id'  => $request->priority_id,
        'source_id'  => $request->source_id,
        'status_id'  => 1,
        'description'  => $request->description,
        'created_by'  => auth()->user()->id
      ];

      $case = Cases::create($data);

      foreach ($request->product_items as $key => $item) {
        CaseItem::create([
          'uid_case' => $uid_case,
          'product_id' => $item['product_id'],
          'qty' => $item['qty'],
        ]);
      }

      createNotification(
        'TCTCASE',
        [
          'user_id' => $case->contact
        ],
        [
          'name' => $case->contact_name,
          'nomor_tiket' => $case->title,
          'brand' => 'FIMTY'
        ],
        [
          'brand_id' => 1
        ]
      );
      DB::commit();
      return response()->json([
        'status' => 'success',
        'data' => [],
        'message' => 'Data Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json([
        'status' => 'error',
        'data' => [],
        'message' => 'Data Gagal Disimpan',
        'dev' => $th->getMessage()
      ], 400);
    }
  }

  public function updateCase(Request $request, $uid_case)
  {
    try {
      DB::beginTransaction();
      $case = Cases::where('uid_case', $uid_case)->first();
      $data = [
        'contact'  => $request->contact,
        'type_id'  => $request->type_id,
        'category_id'  => $request->category_id,
        'priority_id'  => $request->priority_id,
        'source_id'  => $request->source_id,
        'status_id'  => $request->status_id,
        'description'  => $request->description,
        'updated_by'  => auth()->user()->id
      ];

      $case->update($data);
      $case->items()->delete();
      foreach ($request->product_items as $key => $item) {
        CaseItem::create([
          'uid_case' => $uid_case,
          'product_id' => $item['product_id'],
          'qty' => $item['qty'],
        ]);
      }

      DB::commit();
      return response()->json([
        'status' => 'success',
        'data' => [],
        'message' => 'Data Berhasil Disimpan'
      ]);
    } catch (\Throwable $th) {
      DB::rollBack();
      return response()->json([
        'status' => 'error',
        'data' => [],
        'message' => 'Data Gagal Disimpan'
      ], 400);
    }
  }

  private function generateTitle()
  {
    $year = date('Y');
    $title = 'CASE/' . $year . '/';
    $data = DB::select("SELECT * FROM `tbl_case_masters` where title like '%$title%' order by id desc limit 0,1");
    $total = count($data);
    $nomor = 'CASE/' . $year . '/' . '000000001';
    if ($total > 0) {
      foreach ($data as $rw) {
        $awal = substr($rw->title, -9);
        $next = sprintf("%09d", ((int)$awal + 1));
        $nomor = 'CASE/' . $year . '/' . $next;
      }
    }
    return $nomor;
  }
}
