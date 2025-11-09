<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Jobs\CreateLogQueue;
use App\Models\UrlShortener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UrlShortenerController extends Controller
{
    public function index($url_id = null)
    {
        return view('spa.spa-index');
    }

    public function listUrlShortener(Request $request)
    {
        $search = $request->search;
        $status = $request->status;

        $urlShortener = UrlShortener::with('creator');

        if ($search) {
            $urlShortener->where(function ($query) use ($search) {
                $query->where('title', 'like', "%$search%")
                    ->orWhere('original_url', 'like', "%$search%")
                    ->orWhere('short_code', 'like', "%$search%");
            });
        }

        if ($status !== null && $status !== '') {
            $urlShortener->where('status', $status);
        }

        $urlShorteners = $urlShortener->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? $request->perpage ?? 10);

        return response()->json([
            'status' => 'success',
            'data' => $urlShorteners,
            'message' => 'List URL Shortener'
        ]);
    }

    public function getDetailUrlShortener($url_id)
    {
        $urlShortener = UrlShortener::with('creator')->find($url_id);

        if (!$urlShortener) {
            return response()->json([
                'status' => 'error',
                'message' => 'URL Shortener tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $urlShortener,
            'message' => 'Detail URL Shortener'
        ]);
    }

    public function saveUrlShortener(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'original_url' => 'required|url',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_code' => 'nullable|string|max:255|unique:url_shorteners,short_code',
            'parameters' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
            'status' => 'nullable' // Allow both boolean and string
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Extract parameters from original URL if not provided
            $originalParams = UrlShortener::extractParametersFromUrl($request->original_url);
            $cleanUrl = UrlShortener::getCleanUrl($request->original_url);

            // Merge provided parameters with extracted ones
            $parameters = array_merge($originalParams, $request->parameters ?? []);

            // Handle status conversion from string to boolean

            $data = [
                'original_url' => $cleanUrl,
                'title' => $request->title,
                'description' => $request->description,
                'short_code' => $request->short_code ?: UrlShortener::generateShortCode(),
                'parameters' => !empty($parameters) ? $parameters : null,
                'expires_at' => $request->expires_at,
                'status' => 1,
                'created_by' => auth()->id()
            ];

            $urlShortener = UrlShortener::create($data);

            $dataLog = [
                'log_type' => '[fis-dev]url_shortener',
                'log_description' => 'Create URL Shortener - ' . $urlShortener->id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $urlShortener->fresh(),
                'message' => 'URL Shortener berhasil dibuat'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Create URL Shortener Error: ' . $th->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'URL Shortener gagal dibuat',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateUrlShortener(Request $request, $url_id)
    {
        $urlShortener = UrlShortener::find($url_id);

        if (!$urlShortener) {
            return response()->json([
                'status' => 'error',
                'message' => 'URL Shortener tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'original_url' => 'required|url',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'short_code' => 'nullable|string|max:255|unique:url_shorteners,short_code,' . $url_id,
            'parameters' => 'nullable|array',
            'expires_at' => 'nullable|date|after:now',
            'status' => 'nullable' // Allow both boolean and string
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Extract parameters from original URL if not provided
            $originalParams = UrlShortener::extractParametersFromUrl($request->original_url);
            $cleanUrl = UrlShortener::getCleanUrl($request->original_url);

            // Merge parameters if provided
            $parameters = array_merge($originalParams, $request->parameters ?? []);

            // Handle status conversion from string to boolean
            $status = $request->status;
            if (is_string($status)) {
                $status = $status === 'active';
            } else {
                $status = $status ?? true;
            }

            $data = [
                'original_url' => $cleanUrl,
                'title' => $request->title,
                'description' => $request->description,
                'parameters' => !empty($parameters) ? $parameters : null,
                'expires_at' => $request->expires_at,
                'status' => $status
            ];

            // Only update short_code if provided and different
            if ($request->short_code && $request->short_code !== $urlShortener->short_code) {
                $data['short_code'] = $request->short_code;
            }

            $urlShortener->update($data);

            $dataLog = [
                'log_type' => '[fis-dev]url_shortener',
                'log_description' => 'Update URL Shortener - ' . $url_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            DB::commit();

            return response()->json([
                'status' => 'success',
                'data' => $urlShortener->fresh(),
                'message' => 'URL Shortener berhasil diupdate'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error('Update URL Shortener Error: ' . $th->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'URL Shortener gagal diupdate',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function deleteUrlShortener($url_id)
    {
        $urlShortener = UrlShortener::find($url_id);

        if (!$urlShortener) {
            return response()->json([
                'status' => 'error',
                'message' => 'URL Shortener tidak ditemukan'
            ], 404);
        }

        try {
            $urlShortener->delete();

            $dataLog = [
                'log_type' => '[fis-dev]url_shortener',
                'log_description' => 'Delete URL Shortener - ' . $url_id,
                'log_user' => auth()->user()->name,
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            return response()->json([
                'status' => 'success',
                'message' => 'URL Shortener berhasil dihapus'
            ]);
        } catch (\Throwable $th) {
            Log::error('Delete URL Shortener Error: ' . $th->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'URL Shortener gagal dihapus'
            ], 500);
        }
    }

    public function generateShortCode(Request $request)
    {
        $length = $request->length ?? 6;
        $shortCode = UrlShortener::generateShortCode($length);

        return response()->json([
            'status' => 'success',
            'data' => ['short_code' => $shortCode],
            'message' => 'Short code generated'
        ]);
    }

    public function extractUrlParameters(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'URL tidak valid'
            ], 422);
        }

        $parameters = UrlShortener::extractParametersFromUrl($request->url);
        $cleanUrl = UrlShortener::getCleanUrl($request->url);

        return response()->json([
            'status' => 'success',
            'data' => [
                'clean_url' => $cleanUrl,
                'parameters' => $parameters
            ],
            'message' => 'Parameters extracted'
        ]);
    }

    /**
     * Get URL data by short code for external redirector
     * 
     * @param string $shortCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getByShortCode($shortCode)
    {
        try {
            $urlShortener = UrlShortener::where('short_code', $shortCode)
                // ->where('status', 1) // Only active URLs
                ->first();

            if (!$urlShortener) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'URL tidak ditemukan atau tidak aktif'
                ], 404);
            }

            // Check if URL is expired
            if ($urlShortener->expires_at && now() > $urlShortener->expires_at) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'URL telah kedaluwarsa'
                ], 410); // 410 Gone
            }

            // Increment click count
            $urlShortener->increment('click_count');

            // Log the access
            $dataLog = [
                'log_type' => '[fis-dev]url_shortener_access',
                'log_description' => 'URL Shortener Accessed - ' . $shortCode . ' -> ' . $urlShortener->original_url,
                'log_user' => 'external_redirector',
            ];
            CreateLogQueue::dispatch($dataLog)->onQueue('queue-log');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'short_code' => $urlShortener->short_code,
                    'original_url' => $urlShortener->original_url,
                    'title' => $urlShortener->title,
                    'description' => $urlShortener->description,
                    'parameters' => $urlShortener->parameters,
                    'expires_at' => $urlShortener->expires_at,
                    'click_count' => $urlShortener->click_count,
                    'created_at' => $urlShortener->created_at
                ],
                'message' => 'URL data retrieved successfully'
            ]);
        } catch (\Throwable $th) {
            Log::error('Get URL by Short Code Error: ' . $th->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data URL'
            ], 500);
        }
    }
}
