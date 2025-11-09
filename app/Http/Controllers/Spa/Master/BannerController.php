<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\Banner;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Str;


class BannerController extends Controller
{
    public function index($banner_id = null)
    {
        return view('spa.spa-index');
    }

    public function listBanner(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $banner =  Banner::query();
        if ($search) {
            $banner->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%");
            });
        }

        if ($status) {
            $banner->whereIn('status', $status);
        }


        $banners = $banner->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $banners,
            'message' => 'List Banner'
        ]);
    }


    public function getDetailBanner($banner_id)
    {
        $brand = Banner::with('brands')->find($banner_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Banner'
        ]);
    }

    public function saveBanner(Request $request)
    {
        try {
            DB::beginTransaction();
            $image = $this->uploadImage($request, 'image');
            $brand_id = json_decode($request->brand_id, true);
            $data = [
                'title'  => $request->title,
                'url'  => $request->url,
                'image'  => $image,
                'sales_channel'  => $request->sales_channel,
                'slug'  => Str::slug($request->title),
                'description'  => $request->description,
                'brand_id'  => is_array($brand_id) && count($brand_id) > 0 ? $brand_id[0] : 1,
                'status'  => $request->status
            ];

            $banner = Banner::create($data);
            $banner->brands()->attach($brand_id);

            $dataLog = [
                'log_type' => '[fis-dev]master_banner',
                'log_description' => 'Create Master Banner - ' . $banner->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Banner Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Banner Gagal Disimpan'
            ], 400);
        }
    }

    public function updateBanner(Request $request, $banner_id)
    {
        try {
            DB::beginTransaction();

            $brand_id = json_decode($request->brand_id, true);
            $row = Banner::find($banner_id);

            if (!$row) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Banner tidak ditemukan'
                ], 404);
            }

            $data = [
                'title' => $request->title,
                'url' => $request->url,
                'slug' => Str::slug($request->title),
                'description' => $request->description,
                'sales_channel' => $request->sales_channel,
                'brand_id' => is_array($brand_id) && count($brand_id) > 0 ? $brand_id[0] : 1,
                'status' => $request->status,
            ];

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = 'uploads/' . time() . '-' . $file->getClientOriginalName();

                if (!$file->isValid()) {
                    throw new \Exception("File tidak valid atau korup");
                }

                try {
                    //$image = $this->uploadImage($request, 'image');

                    Storage::disk('s3')->putFileAs('uploads', $file, time() . '-' . $file->getClientOriginalName(), 'public');
                    $data['image'] = $filename;

                    // if ($row->image && Storage::disk('s3')->exists($row->image)) {
                    //     Storage::disk('s3')->delete($row->image);
                    // }
                } catch (\Exception $e) {
                    DB::rollback();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Image upload failed',
                        'error' => $e->getMessage()
                    ], 400);
                }
            }

            $row->update($data);
            $row->brands()->sync($brand_id);

            $dataLog = [
                'log_type' => '[fis-dev]master_banner',
                'log_description' => 'Update Master Banner - ' . $banner_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Banner Berhasil Disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Update Banner Error: ' . $th->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Data Banner Gagal Disimpan',
                'error' => $th->getMessage()
            ], 400);
        }
    }

    public function deleteBanner($banner_id)
    {
        $banner = Banner::find($banner_id);
        $banner->brands()->detach();
        $banner->delete();
        $dataLog = [
            'log_type' => '[fis-dev]master_banner',
            'log_description' => 'Delete Master Banner - ' . $banner_id,
            'log_user' => auth()->user()->name,
        ];
        CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');
        return response()->json([
            'status' => 'success',
            'message' => 'Data Banner berhasil dihapus'
        ]);
    }

    public function uploadImage($request, $path)
    {
        if (!$request->hasFile($path)) {
            throw new \Exception("File not found in request");
        }

        $file = $request->file($path);
        if (!$file->isValid()) {
            throw new \Exception("Uploaded file is not valid");
        }

        try {
            $filename = 'uploads/' . time() . '-' . $file->getClientOriginalName();
            $path = Storage::disk('b2')->put($filename, file_get_contents($file), 'public');

            if (!$path) {
                throw new \Exception("Failed to upload image to Backblaze B2");
            }

            return $filename;
        } catch (\Exception $e) {
            Log::error("Backblaze B2 Upload Error: " . $e->getMessage());
            throw new \Exception("Failed to upload image to B2: " . $e->getMessage());
        }
    }
}
