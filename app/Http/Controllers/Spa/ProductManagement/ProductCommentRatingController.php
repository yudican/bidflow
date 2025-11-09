<?php

namespace App\Http\Controllers\Spa\ProductManagement;

use App\Http\Controllers\Controller;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;

class ProductCommentRatingController extends Controller
{
    public function index($comment_rating_id = null)
    {
        return view('spa.spa-index');
    }

    public function listCommentRating(Request $request)
    {
        $search = $request->search;
        $product_id = $request->product_id;
        $row =  TransactionDetail::query()->whereHas('transaction', function ($query) {
            $query->whereHas('commentRating');
        });
        if ($search) {
            $row->where(function ($query) use ($search) {
                $query->where('comment', 'like', "%$search%");
                $query->orWhereHas('user', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                });
            });
        }

        if ($product_id) {
            $row->where('product_id', $product_id);
        }

        $rows = $row->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => tap($rows)->map(function ($item) {
                $item['product_name'] = $item->variant ? $item->variant->name : '-';
                $item['product_image'] = $item->variant ? $item->variant->image_url : '-';
                $item['user_name'] = $item->transaction->user ? $item->transaction->user->name : '-';
                $item['invoice_id'] = $item->invoice_id;
                $item['comment'] = $item->comment;
                $item['rate'] = $item->rating;
                return $item;
            }),
            'message' => 'List Comment Rating'
        ]);
    }
}
