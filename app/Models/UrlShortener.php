<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class UrlShortener extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_url',
        'short_code',
        'title',
        'description',
        'parameters',
        'click_count',
        'status',
        'expires_at',
        'created_by'
    ];

    protected $casts = [
        'parameters' => 'array',
        'expires_at' => 'datetime',
        'status' => 'boolean'
    ];

    protected $appends = ['short_url', 'final_url'];

    /**
     * Relationship with User model
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate short URL with base domain
     */
    public function getShortUrlAttribute()
    {
        return 'https://utm-eight.vercel.app/' . $this->short_code;
    }

    /**
     * Get final URL with merged parameters
     */
    public function getFinalUrlAttribute()
    {
        return $this->buildFinalUrl();
    }

    /**
     * Generate unique short code
     */
    public static function generateShortCode($length = 6)
    {
        do {
            $shortCode = Str::random($length);
        } while (self::where('short_code', $shortCode)->exists());

        return $shortCode;
    }

    /**
     * Extract parameters from URL
     */
    public static function extractParametersFromUrl($url)
    {
        $parsedUrl = parse_url($url);
        $parameters = [];

        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $parameters);
        }

        return $parameters;
    }

    /**
     * Get clean URL without parameters
     */
    public static function getCleanUrl($url)
    {
        $parsedUrl = parse_url($url);
        $cleanUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];

        if (isset($parsedUrl['port'])) {
            $cleanUrl .= ':' . $parsedUrl['port'];
        }

        if (isset($parsedUrl['path'])) {
            $cleanUrl .= $parsedUrl['path'];
        }

        return $cleanUrl;
    }

    /**
     * Build final URL with parameters
     */
    public function buildFinalUrl($additionalParams = [])
    {
        $cleanUrl = self::getCleanUrl($this->original_url);
        $originalParams = self::extractParametersFromUrl($this->original_url);
        $storedParams = $this->parameters ?? [];

        // Merge parameters: stored params override original, additional params override both
        $finalParams = array_merge($originalParams, $storedParams, $additionalParams);

        if (!empty($finalParams)) {
            $cleanUrl .= '?' . http_build_query($finalParams);
        }

        return $cleanUrl;
    }

    /**
     * Increment click count
     */
    public function incrementClickCount()
    {
        $this->increment('click_count');
    }

    /**
     * Check if URL is expired
     */
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if URL is active
     */
    public function isActive()
    {
        return $this->status && !$this->isExpired();
    }

    /**
     * Scope for active URLs
     */
    public function scopeActive($query)
    {
        return $query->where('status', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for user's URLs
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('created_by', $userId);
    }
}
