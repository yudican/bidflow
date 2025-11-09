<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\CompanyAccount;
use App\Models\ProductCarton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class ProductCartonController extends Controller
{
    public function index($product_carton_id = null)
    {
        return view('spa.spa-index');
    }

    public function listProductCarton(Request $request)
    {
        $search = $request->search;

        $product_carton =  ProductCarton::query();
        if ($search) {
            $product_carton->where(function ($query) use ($search) {
                $query->where('sku', 'like', "%$search%");
                $query->orWhere('product_name', 'like', "%$search%");
            });
        }

        $product_cartons = $product_carton->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $product_cartons,
            'message' => 'List Produk Karton'
        ]);
    }


    public function getDetailProductCarton($product_carton_id)
    {
        $product_account = ProductCarton::find($product_carton_id);
        return response()->json([
            'status' => 'success',
            'data' => $product_account,
            'message' => 'Detail Produk Karton'
        ]);
    }

    public function saveProductCarton(Request $request)
    {
        // Validate the request
        $validatedData = $request->validate([
            'vendor_id' => 'required',
            'sku' => 'required|unique:product_cartons,sku',
            'product_name' => 'required',
            'qty' => 'required',
            'moq' => 'required',
        ], [
            'sku.unique' => 'Maaf, SKU yang Anda masukkan sudah ada. Harap masukkan SKU yang berbeda.',
        ]);

        try {
            DB::beginTransaction();
            
            // Prepare data
            $data = [
                'vendor_id'  => $validatedData['vendor_id'],
                'sku'  => $validatedData['sku'],
                'product_name'  => $validatedData['product_name'],
                'qty'  => $validatedData['qty'],
                'moq'  => $validatedData['moq'],
                'created_by' => auth()->user()->id
            ];

            // Create the product carton
            ProductCarton::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Produk karton berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => 'Data Gagal Disimpan'
            ], 400);
        }
    }

    public function updateProductCarton(Request $request, $product_carton_id)
    {
        try {
            DB::beginTransaction();

            $data = [
                'vendor_id'  => $request->vendor_id,
                'sku'  => $request->sku,
                'product_name'  => $request->product_name,
                'qty'  => $request->qty,
                'moq'  => $request->moq
            ];
            $row = ProductCarton::find($product_carton_id);
            $row->update($data);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Produk karton berhasil disimpan'
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

    public function deleteProductCarton($product_carton_id)
    {
        $product_carton = ProductCarton::find($product_carton_id);
        $product_carton->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
