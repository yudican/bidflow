<?php

namespace App\Http\Controllers\Spa;

use App\Jobs\CreateLogQueue;
use App\Http\Controllers\Controller;
use App\Models\Kecamatan;
use App\Models\LogApproveFinance;
use App\Models\LogError;
use App\Models\Role;
use App\Models\BarcodeMaster;
use App\Models\BarcodeChild;
use App\Models\BarcodeHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BarcodeController extends Controller
{
    public function index($transaction_id = null)
    {
        return view('spa.spa-index');
    }

    public function listBarcode(Request $request)
    {
        $search = $request->search;
        $created_at = $request->created_at;
        $status = $request->status;
        $status_delivery = $request->status_delivery;
        $payment_method = $request->payment_method;
        $type = $request->type;
        $action = $request->action;
        $user = auth()->user();
        $role = $user->role;
        $stage = $request->stage;

        $transaction = BarcodeMaster::query();
        if ($search) {
            $transaction->where('id_transaksi', 'like', "%$search%");
            $transaction->orWhereHas('user', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
            $transaction->orWhereHas('userCreated', function ($query) use ($search) {
                $query->where('users.name', 'like', "%$search%");
            });
        }

        
        if ($stage == 'on-production') {
            $transaction->where('status', 'On Production');
        } elseif ($stage == 'inbound') {
            $transaction->where('status', 'Inbound');
        } elseif ($stage == 'transfer') {
            $transaction->where('status', 'Transfer');
        } elseif ($stage == 'outbound') {
            $transaction->where('status', 'Outbound');
        }
        
        // end stage
        if ($status) {
            $transaction->whereIn('status', $status);
        }

        if ($created_at) {
            $transaction->whereBetween('created_at', $created_at);
        }

        $transactions = $transaction->orderBy('created_at', 'desc')->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }

    public function getBarcodeDetail($barcode_id)
    {
        $barcode = BarcodeMaster::with([
            'barcodeChildren', 'barcodeHistory'
        ])->find($barcode_id);

        return response()->json([
            'status' => 'success',
            'data' => $barcode
        ]);
    }

    public function reset($id)
    {
        $barcodeChild = BarcodeChild::where('barcode', $id)->first();
        $newStatus = $this->backStatus($barcodeChild->status);
        $barcodeChild->status = $newStatus;
        $barcodeChild->save();

        $barcodeMaster = BarcodeMaster::find($barcodeChild->parent_id);
        $barcodeMaster->status = $newStatus;
        $barcodeMaster->save();

        // Simpan data ke BarcodeHistory
        $history = new BarcodeHistory();
        $history->barcode_id = $barcodeMaster->id;
        $history->activity = 'Barcode '. $barcodeChild->barcode.' - reset status menjadi '.$newStatus;
        $history->created_by = auth()->user()->id;
        $history->save();

        return response()->json([
            'status' => 'success',
            'data' => $barcodeChild
        ]);
    }

    public function uploadImage(Request $request)
    {
        $barcode = BarcodeChild::where('barcode', $request->id)->first();
        $newStatus = $this->nextStatus($barcode->status);
        if (!$request->hasFile('file')) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        
        $file = Storage::disk('s3')->put('upload/barcode_file', $request->file, 'public');
        $barcode->update(['status' => $newStatus, 'file' => $file]);

        $history = new BarcodeHistory();
        $history->barcode_id = $barcode->parent_id;
        $history->activity = 'Barcode '. $barcode->barcode.' - update manual status menjadi '.$newStatus;
        $history->created_by = auth()->user()->id;
        $history->save();

        return $file;
    }

    public function backStatus($status)
    {
        switch ($status) {
            case 'Inbound':
              return 'On Production';
              break;
            case 'Transfer':
              return 'Inbound';
              break;
            case 'Outbound':
              return 'Transfer';
              break;
            default:
              return 'On Production';
              break;
        }
    }

    public function nextStatus($status)
    {
        switch ($status) {
            case 'On Production':
              return 'Inbound';
              break;
            case 'Inbound':
              return 'Transfer';
              break;
            case 'Transfer':
              return 'Outbound';
              break;
            default:
              return 'On Production';
              break;
        }
    }

}
