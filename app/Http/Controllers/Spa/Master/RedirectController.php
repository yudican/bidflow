<?php

namespace App\Http\Controllers\Spa\Master;

use App\Http\Controllers\Controller;
use App\Models\UrlShortener;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class RedirectController extends Controller
{
    /**
     * Handle short URL redirect
     */
    public function redirect(Request $request, string $short_code): RedirectResponse|JsonResponse
    {
        try {
            // Find the URL shortener by short code
            $urlShortener = UrlShortener::where('short_code', $short_code)
                ->where('status', 'active')
                ->first();

            if (!$urlShortener) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Short URL not found or inactive'
                ], 404);
            }

            // Check if URL is expired
            if ($urlShortener->isExpired()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Short URL has expired'
                ], 410);
            }

            // Increment click count
            $urlShortener->incrementClickCount();

            // Get additional parameters from request
            $additionalParams = $request->query();
            
            // Build final URL with merged parameters
            $finalUrl = $urlShortener->buildFinalUrl($additionalParams);

            // Log the redirect
            Log::info('URL redirect', [
                'action' => 'url_redirect',
                'short_code' => $short_code,
                'original_url' => $urlShortener->original_url,
                'final_url' => $finalUrl,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
                'click_count' => $urlShortener->click_count,
                'created_at' => now()
            ]);

            // Redirect to the final URL
            return redirect($finalUrl);

        } catch (\Exception $e) {
            Log::error('URL redirect error: ' . $e->getMessage(), [
                'short_code' => $short_code,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing the redirect'
            ], 500);
        }
    }

    /**
     * Preview short URL details without redirecting
     */
    public function preview(Request $request, string $short_code): JsonResponse
    {
        try {
            // Find the URL shortener by short code
            $urlShortener = UrlShortener::where('short_code', $short_code)
                ->with('creator:id,name,email')
                ->first();

            if (!$urlShortener) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Short URL not found'
                ], 404);
            }

            // Get additional parameters from request
            $additionalParams = $request->query();
            
            // Build final URL with merged parameters
            $finalUrl = $urlShortener->buildFinalUrl($additionalParams);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $urlShortener->id,
                    'short_code' => $urlShortener->short_code,
                    'short_url' => $urlShortener->short_url,
                    'original_url' => $urlShortener->original_url,
                    'final_url' => $finalUrl,
                    'title' => $urlShortener->title,
                    'description' => $urlShortener->description,
                    'parameters' => $urlShortener->parameters,
                    'click_count' => $urlShortener->click_count,
                    'status' => $urlShortener->status,
                    'expires_at' => $urlShortener->expires_at,
                    'is_expired' => $urlShortener->isExpired(),
                    'is_active' => $urlShortener->isActive(),
                    'creator' => $urlShortener->creator,
                    'created_at' => $urlShortener->created_at,
                    'updated_at' => $urlShortener->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('URL preview error: ' . $e->getMessage(), [
                'short_code' => $short_code,
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while fetching URL details'
            ], 500);
        }
    }
}