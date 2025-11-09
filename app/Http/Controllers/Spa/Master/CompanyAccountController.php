<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\CompanyAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class CompanyAccountController extends Controller
{
    public function index($company_account_id = null)
    {
        return view('spa.spa-index');
    }

    public function listCompanyAccount(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $company_account =  CompanyAccount::query();
        if ($search) {
            $company_account->where(function ($query) use ($search) {
                $query->where('account_code', 'like', "%$search%");
                $query->orWhere('account_name', 'like', "%$search%");
                $query->orWhere('account_phone', 'like', "%$search%");
                $query->orWhere('account_email', 'like', "%$search%");
                $query->orWhere('account_description', 'like', "%$search%");
            });
        }

        if ($status) {
            $company_account->whereIn('status', $status);
        }

        $company_accounts = $company_account->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $company_accounts,
            'message' => 'List Brand'
        ]);
    }


    public function getDetailCompanyAccount($company_account_id)
    {
        $company_account = CompanyAccount::find($company_account_id);
        return response()->json([
            'status' => 'success',
            'data' => $company_account,
            'message' => 'Detail Brand'
        ]);
    }

    public function saveCompanyAccount(Request $request)
    {
        $request->validate([
            'account_code' => 'required|unique:company_accounts,account_code',
        ], [
            'account_code.unique' => 'Maaf, Company Account Code yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.',
            'account_code.required' => 'Kode akun wajib diisi.',
        ]);

        try {
            DB::beginTransaction();
            $data = [
                'account_code'  => $request->account_code,
                'account_name'  => $request->account_name,
                'account_phone'  => $request->account_phone,
                'account_email'  => $request->account_email,
                'account_address'  => $request->account_address,
                'provinsi_id'  => $request->provinsi_id,
                'kabupaten_id'  => $request->kabupaten_id,
                'kecamatan_id'  => $request->kecamatan_id,
                'kelurahan_id'  => $request->kelurahan_id,
                'kodepos'  => $request->kodepos,
                'status'  => $request->status,
                'account_description'  => $request->account_description,
            ];

            if ($request->account_logo) {
                $logo = $this->uploadImage($request, 'account_logo');
                $data['account_logo'] = $logo;
            }

            CompanyAccount::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan'
            ], 400);
        }
    }

    public function updateCompanyAccount(Request $request, $company_account_id)
    {
        $request->validate([
            'account_code' => 'required|unique:company_accounts,account_code,' . $company_account_id,
        ], [
            'account_code.required' => 'Kode akun wajib diisi.',
            'account_code.unique' => 'Maaf, Company Account Code yang Anda masukkan sudah terdaftar. Harap masukkan kode yang berbeda.',
        ]);        

        try {
            DB::beginTransaction();

            $data = [
                'account_code'  => $request->account_code,
                'account_name'  => $request->account_name,
                'account_phone'  => $request->account_phone,
                'account_email'  => $request->account_email,
                'account_address'  => $request->account_address,
                'provinsi_id'  => $request->provinsi_id,
                'kabupaten_id'  => $request->kabupaten_id,
                'kecamatan_id'  => $request->kecamatan_id,
                'kelurahan_id'  => $request->kelurahan_id,
                'kodepos'  => $request->kodepos,
                'account_description'  => $request->account_description,
                'status'  => $request->status,
            ];
            $row = CompanyAccount::find($company_account_id);

            if ($request->account_logo) {
                $account_logo = $this->uploadImage($request, 'account_logo');
                $data = ['account_logo' => $account_logo];
                if (Storage::exists('public/' . $request->account_logo)) {
                    Storage::delete('public/' . $request->account_logo);
                }
            }

            $row->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function deleteCompanyAccount($company_account_id)
    {
        $company_account = CompanyAccount::find($company_account_id);
        $company_account->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function updateStatusCompanyAccount(Request $request, $company_account_id)
    {
        $company_account = CompanyAccount::find($company_account_id);
        $company_account->update([
            'status' => $company_account->status == 1 ? 0 : 1
        ]);
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil diupdate'
        ]);
    }

    public function uploadImage($request, $path)
    {
        if (!$request->hasFile($path)) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }

        $file = $request->file($path);
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }

        $file = Storage::disk('s3')->put('upload/master/company_account', $request[$path], 'public');
        return $file;
    }
}
