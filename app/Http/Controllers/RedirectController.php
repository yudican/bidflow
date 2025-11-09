<?php

namespace App\Http\Controllers;

use App\Models\UrlShortener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RedirectController extends Controller
{
    public function redirect(Request $request, $shortCode)
    {
        try {
            $urlShortener = UrlShortener::where('short_code', $shortCode)->first();

            if (!$urlShortener) {
                return response()->view('errors.404', [], 404);
            }

            if (!$urlShortener->isActive()) {
                return response()->view('errors.410', [
                    'message' => 'This short URL has expired or is inactive.'
                ], 410);
            }

            // Increment click count
            $urlShortener->incrementClickCount();

            // Get additional parameters from request
            $additionalParams = $request->query();

            // Build final URL with merged parameters
            $finalUrl = $urlShortener->buildFinalUrl($additionalParams);

            // Log the redirect for analytics
            Log::info('URL Redirect', [
                'short_code' => $shortCode,
                'original_url' => $urlShortener->original_url,
                'final_url' => $finalUrl,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'referer' => $request->header('referer')
            ]);

            return redirect($finalUrl);

        } catch (\Throwable $th) {
            Log::error('Redirect Error: ' . $th->getMessage(), [
                'short_code' => $shortCode,
                'error' => $th->getMessage()
            ]);

            return response()->view('errors.500', [], 500);
        }
    }

    public function preview(Request $request, $shortCode)
    {
        try {
            $urlShortener = UrlShortener::where('short_code', $shortCode)->first();

            if (!$urlShortener) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Short URL not found'
                ], 404);
            }

            if (!$urlShortener->isActive()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'This short URL has expired or is inactive'
                ], 410);
            }

            // Get additional parameters from request
            $additionalParams = $request->query();

            // Build final URL with merged parameters
            $finalUrl = $urlShortener->buildFinalUrl($additionalParams);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'short_url' => $urlShortener->short_url,
                    'original_url' => $urlShortener->original_url,
                    'final_url' => $finalUrl,
                    'title' => $urlShortener->title,
                    'description' => $urlShortener->description,
                    'parameters' => $urlShortener->parameters,
                    'click_count' => $urlShortener->click_count,
                    'created_at' => $urlShortener->created_at,
                    'expires_at' => $urlShortener->expires_at
                ],
                'message' => 'URL preview'
            ]);

        } catch (\Throwable $th) {
            Log::error('Preview Error: ' . $th->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error'
            ], 500);
        }
    }
}