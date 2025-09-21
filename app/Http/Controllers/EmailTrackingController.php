<?php

namespace App\Http\Controllers;

use App\Models\FaxJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailTrackingController extends Controller
{
    /**
     * Track email click and redirect to fax step
     */
    public function trackClick(Request $request, FaxJob $faxJob)
    {
        try {
            // Extract UTM parameters and email type
            $emailType = $request->get('email_type', 'unknown');
            $utmParams = [
                'utm_source' => $request->get('utm_source'),
                'utm_medium' => $request->get('utm_medium'),
                'utm_campaign' => $request->get('utm_campaign'),
                'utm_term' => $request->get('utm_term'),
                'utm_content' => $request->get('utm_content'),
            ];

            // Track the click
            $faxJob->trackEmailClick($emailType, $utmParams);

            // Log the click for analytics
            Log::info('Email link clicked', [
                'fax_job_id' => $faxJob->id,
                'email_type' => $emailType,
                'utm_params' => $utmParams,
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'timestamp' => now()->toISOString()
            ]);

            // Build redirect URL with all original parameters
            $redirectParams = $request->except(['email_type']); // Remove email_type from redirect
            $redirectUrl = route('fax.step2', array_merge(['faxJob' => $faxJob->hash], $redirectParams));

            return redirect($redirectUrl);

        } catch (\Exception $e) {
            Log::error('Email tracking failed', [
                'fax_job_id' => $faxJob->id ?? 'unknown',
                'error' => $e->getMessage(),
                'request_params' => $request->all()
            ]);

            // Fallback to direct fax step2 if tracking fails
            return redirect()->route('fax.step2', $faxJob->hash);
        }
    }

    /**
     * Generate tracking URL for emails
     */
    public static function generateTrackingUrl(FaxJob $faxJob, string $emailType, array $additionalParams = [])
    {
        $baseParams = [
            'faxJob' => $faxJob->hash,
            'email_type' => $emailType,
            'utm_source' => 'email',
            'utm_medium' => 'email_reminder',
            'utm_campaign' => $emailType === 'early_reminder' ? 'early_abandonment' : 'discount_abandonment',
            'utm_term' => 'fax_reminder',
            'utm_content' => $emailType . '_cta_button'
        ];

        $params = array_merge($baseParams, $additionalParams);

        return route('email.track', $params);
    }

    /**
     * Get email tracking analytics for admin dashboard
     */
    public function getEmailAnalytics(Request $request)
    {
        // Verify admin access (you might want to add proper authentication)
        // This is a basic example - implement proper admin auth

        $days = $request->get('days', 30);
        $startDate = now()->subDays($days);

        $analytics = FaxJob::where('created_at', '>=', $startDate)
            ->whereNotNull('email_clicks')
            ->get()
            ->map(function ($faxJob) {
                $stats = $faxJob->getEmailClickStats();
                return [
                    'fax_job_id' => $faxJob->id,
                    'created_at' => $faxJob->created_at,
                    'status' => $faxJob->status,
                    'early_reminder_sent' => $faxJob->early_reminder_sent,
                    'reminder_sent' => $faxJob->reminder_email_sent,
                    'clicks' => $stats,
                    'conversion_rate' => $faxJob->status === 'paid' ? 1 : 0
                ];
            });

        $summary = [
            'total_fax_jobs' => $analytics->count(),
            'total_clicks' => $analytics->sum('clicks.total_clicks'),
            'early_reminder_clicks' => $analytics->sum('clicks.early_reminder_clicks'),
            'discount_reminder_clicks' => $analytics->sum('clicks.reminder_clicks'),
            'conversions' => $analytics->sum('conversion_rate'),
            'click_to_conversion_rate' => $analytics->where('clicks.total_clicks', '>', 0)->sum('conversion_rate') / max(1, $analytics->where('clicks.total_clicks', '>', 0)->count())
        ];

        return response()->json([
            'summary' => $summary,
            'details' => $analytics,
            'period_days' => $days
        ]);
    }
}