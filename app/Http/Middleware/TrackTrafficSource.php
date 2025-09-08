<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackTrafficSource
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only track on GET requests and if we haven't already captured this session
        if ($request->isMethod('GET') && !session()->has('traffic_source_captured')) {
            $this->captureTrafficSource($request);
        }

        return $next($request);
    }

    /**
     * Capture and store traffic source information
     */
    protected function captureTrafficSource(Request $request): void
    {
        $utmParams = $this->getUtmParameters($request);
        $googleAdsParams = $this->getGoogleAdsParameters($request);
        $referrer = $request->headers->get('referer');
        $trafficSource = $this->determineTrafficSource($utmParams, $googleAdsParams, $referrer);

        $trackingData = [
            'traffic_source' => $trafficSource,
            'utm_source' => $utmParams['utm_source'] ?? null,
            'utm_medium' => $utmParams['utm_medium'] ?? null,
            'utm_campaign' => $utmParams['utm_campaign'] ?? null,
            'utm_term' => $utmParams['utm_term'] ?? null,
            'utm_content' => $utmParams['utm_content'] ?? null,
            'gclid' => $googleAdsParams['gclid'] ?? null,
            'gad_campaignid' => $googleAdsParams['gad_campaignid'] ?? null,
            'gad_source' => $googleAdsParams['gad_source'] ?? null,
            'gbraid' => $googleAdsParams['gbraid'] ?? null,
            'referrer_url' => $referrer,
            'landing_page' => $request->fullUrl(),
            'user_agent' => $request->headers->get('user-agent'),
            'ip_address' => $request->ip(),
            'captured_at' => now()->toISOString(),
        ];

        // Store in session for later use when creating fax jobs
        session([
            'traffic_source_data' => $trackingData,
            'traffic_source_captured' => true
        ]);

        // Log for debugging (can be removed in production)
        \Log::info('Traffic source captured', $trackingData);
    }

    /**
     * Extract UTM parameters from request
     */
    protected function getUtmParameters(Request $request): array
    {
        return [
            'utm_source' => $request->query('utm_source'),
            'utm_medium' => $request->query('utm_medium'),
            'utm_campaign' => $request->query('utm_campaign'),
            'utm_term' => $request->query('utm_term'),
            'utm_content' => $request->query('utm_content'),
        ];
    }

    /**
     * Extract Google Ads parameters from request
     */
    protected function getGoogleAdsParameters(Request $request): array
    {
        return [
            'gclid' => $request->query('gclid'),
            'gad_campaignid' => $request->query('gad_campaignid'),
            'gad_source' => $request->query('gad_source'),
            'gbraid' => $request->query('gbraid'),
            'wbraid' => $request->query('wbraid'), // Google Ads enhanced conversions
        ];
    }

    /**
     * Determine traffic source based on UTM parameters, Google Ads parameters, and referrer
     */
    protected function determineTrafficSource(array $utmParams, array $googleAdsParams, ?string $referrer): string
    {
        // Priority 1: Google Ads detection via gclid or other Google Ads parameters
        if (!empty($googleAdsParams['gclid']) || 
            !empty($googleAdsParams['gad_campaignid']) || 
            !empty($googleAdsParams['gbraid']) || 
            !empty($googleAdsParams['wbraid'])) {
            return 'adwords';
        }

        // Priority 2: UTM-based detection
        if (isset($utmParams['utm_source'])) {
            $utmSource = strtolower($utmParams['utm_source']);
            $utmMedium = strtolower($utmParams['utm_medium'] ?? '');
            
            // Google Ads patterns
            if (in_array($utmSource, ['google', 'googlematchedcontent', 'googleadwords']) || 
                in_array($utmMedium, ['cpc', 'ppc', 'paid', 'google-ads'])) {
                return 'adwords';
            }

            // Other paid sources
            if (in_array($utmMedium, ['cpc', 'ppc', 'paid', 'display', 'banner'])) {
                return 'paid';
            }

            // Social media
            if (in_array($utmSource, ['facebook', 'twitter', 'linkedin', 'instagram', 'youtube']) ||
                in_array($utmMedium, ['social', 'social-media'])) {
                return 'social';
            }

            // Email
            if (in_array($utmMedium, ['email', 'newsletter'])) {
                return 'email';
            }

            // Has UTM but doesn't match other patterns
            return 'campaign';
        }

        // Referrer-based detection
        if ($referrer) {
            $referrerHost = parse_url($referrer, PHP_URL_HOST);
            
            if ($referrerHost) {
                // Google organic search
                if (str_contains($referrerHost, 'google.')) {
                    return 'organic';
                }

                // Other search engines
                $searchEngines = ['bing.com', 'yahoo.com', 'duckduckgo.com', 'baidu.com', 'yandex.'];
                foreach ($searchEngines as $engine) {
                    if (str_contains($referrerHost, $engine)) {
                        return 'organic';
                    }
                }

                // Social media referrals
                $socialSites = ['facebook.com', 'twitter.com', 'linkedin.com', 'instagram.com', 'youtube.com', 't.co'];
                foreach ($socialSites as $social) {
                    if (str_contains($referrerHost, $social)) {
                        return 'social';
                    }
                }

                // External referral
                return 'referral';
            }
        }

        // No referrer or UTM parameters
        return 'direct';
    }
}