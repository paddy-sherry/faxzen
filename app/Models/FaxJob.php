<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaxJob extends Model
{
    protected $fillable = [
        'hash',
        'recipient_number',
        'sender_email',
        'scheduled_time',
        'file_path',
        'file_original_name',
        'amount',
        'payment_intent_id',
        'status',
        'telnyx_fax_id',
        'retry_attempts',
        'retry_stage',
        'last_retry_at',
        'error_message',

        'original_file_size',
        'is_preparing',
        'is_sending',
        'is_delivered',
        'email_sent',
        'failure_email_sent',
        'reminder_email_sent',
        'early_reminder_sent',
        'prepared_at',
        'sending_started_at',
        'delivered_at',
        'email_sent_at',
        'reminder_email_sent_at',
        'early_reminder_sent_at',
        'early_reminder_clicked_at',
        'reminder_clicked_at',
        'email_click_count',
        'email_clicks',
        'last_utm_source',
        'last_utm_medium',
        'last_utm_campaign',
        'discount_code',
        'discount_amount',
        'original_amount',
        'delivery_details',
        'telnyx_status',

        // Traffic source tracking
        'traffic_source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'referrer_url',
        'tracking_data',

        // Cover page fields
        'include_cover_page',
        'cover_sender_name',
        'cover_sender_company',
        'cover_sender_phone',
        'cover_recipient_name',
        'cover_recipient_company',
        'cover_subject',
        'cover_message',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'scheduled_time' => 'datetime',
        'last_retry_at' => 'datetime',
        'prepared_at' => 'datetime',
        'sending_started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'reminder_email_sent_at' => 'datetime',
        'early_reminder_sent_at' => 'datetime',
        'early_reminder_clicked_at' => 'datetime',
        'reminder_clicked_at' => 'datetime',
        'email_clicks' => 'array',
        'discount_amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'is_preparing' => 'boolean',
        'is_sending' => 'boolean',
        'is_delivered' => 'boolean',
        'email_sent' => 'boolean',
        'failure_email_sent' => 'boolean',
        'reminder_email_sent' => 'boolean',
        'early_reminder_sent' => 'boolean',
        'tracking_data' => 'array',
        'include_cover_page' => 'boolean',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_PAYMENT_PENDING = 'payment_pending';
    const STATUS_PAID = 'paid';
    const STATUS_SENT = 'sent';
    const STATUS_FAILED = 'failed';

    public function isPaid()
    {
        return $this->status === self::STATUS_PAID;
    }

    public function canRetry()
    {
        // Allow more retries for busy lines and temporary errors
        $errorMessage = strtolower($this->error_message ?? '');
        $isBusyError = str_contains($errorMessage, 'busy') || str_contains($errorMessage, 'user_busy');
        $isTemporaryError = str_contains($errorMessage, 'timeout') || 
                           str_contains($errorMessage, 'call_dropped') ||
                           str_contains($errorMessage, 'network');
        
        if ($isBusyError || $isTemporaryError) {
            return $this->retry_attempts < 20; // Allow up to 20 retries for these errors
        }
        
        return $this->retry_attempts < 3; // Standard retry limit for other errors
    }

    /**
     * Check if this fax failed due to busy line
     */
    public function isFailedDueToBusyLine()
    {
        $errorMessage = strtolower($this->error_message ?? '');
        return str_contains($errorMessage, 'busy') || str_contains($errorMessage, 'user_busy');
    }

    /**
     * Check if this is a retryable temporary error
     */
    public function hasTemporaryError()
    {
        $errorMessage = strtolower($this->error_message ?? '');
        return str_contains($errorMessage, 'timeout') || 
               str_contains($errorMessage, 'call_dropped') ||
               str_contains($errorMessage, 'network') ||
               str_contains($errorMessage, 'service_unavailable');
    }

    /**
     * Check if this fax is scheduled for future delivery
     */
    public function isScheduled()
    {
        return $this->scheduled_time && $this->scheduled_time->isFuture();
    }

    /**
     * Check if this is a scheduled fax that hasn't been sent yet
     */
    public function isPendingScheduled()
    {
        return $this->isScheduled() && $this->status === self::STATUS_PAID;
    }

    /**
     * Get human-readable scheduled time
     */
    public function getScheduledTimeFormatted($timezone = null)
    {
        if (!$this->scheduled_time) {
            return null;
        }

        $time = $this->scheduled_time;
        if ($timezone) {
            $time = $time->setTimezone($timezone);
        }

        return $time->format('M j, Y \a\t g:i A T');
    }

    /**
     * Get recipient timezone information
     */
    public function getRecipientTimezoneInfo()
    {
        if (!class_exists('\App\Services\TimezoneService')) {
            return null;
        }
        
        return \App\Services\TimezoneService::getBusinessHoursInfo(
            \App\Services\TimezoneService::detectTimezoneFromPhoneNumber($this->recipient_number)
        );
    }

    /**
     * Check if retry is likely delayed due to business hours
     */
    public function isRetryDelayedForBusinessHours()
    {
        return $this->retry_stage === 'business_hours_wait';
    }

    /**
     * Check if in quick retry stage
     */
    public function isInQuickRetryStage()
    {
        return $this->retry_stage === 'quick_retry';
    }

    /**
     * Check if dealing with persistent busy line
     */
    public function isPersistentBusyLine()
    {
        return $this->retry_stage === 'persistent_busy';
    }

    /**
     * Get user-friendly retry stage message
     */
    public function getRetryStageMessage()
    {
        switch ($this->retry_stage) {
            case 'quick_retry':
                return [
                    'title' => '📞 Trying Again Soon',
                    'message' => 'The line was busy, but we\'ll keep trying every few minutes. Most busy lines clear up quickly.',
                    'color' => 'blue'
                ];
            case 'persistent_busy':
                return [
                    'title' => '🔄 Persistent Busy Line',
                    'message' => 'Still busy after several attempts. We\'re continuing to retry during business hours.',
                    'color' => 'yellow'
                ];
            case 'business_hours_wait':
                $recipientInfo = $this->getRecipientTimezoneInfo();
                if ($recipientInfo) {
                    $reason = $recipientInfo['is_weekend'] ? 'It\'s currently weekend' : 'It\'s after business hours';
                    $nextTime = \Carbon\Carbon::parse($recipientInfo['next_business_hour'])->format('D, M j \a\t g:i A T');
                    return [
                        'title' => '🕐 Waiting for Business Hours',
                        'message' => "{$reason} at the recipient's location. We'll try again during business hours for better success rates.",
                        'next_attempt' => $nextTime,
                        'timezone_info' => $recipientInfo,
                        'color' => 'purple'
                    ];
                }
                return [
                    'title' => '🕐 Waiting for Better Timing',
                    'message' => 'We\'re timing our retries for when the recipient is most likely to be available.',
                    'color' => 'purple'
                ];
            default:
                return null;
        }
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($faxJob) {
            if (empty($faxJob->hash)) {
                $faxJob->hash = \Str::random(32);
            }
        });
    }

    /**
     * Get route key name for model binding
     */
    public function getRouteKeyName()
    {
        return 'hash';
    }

    /**
     * Get the current step number (1-4)
     */
    public function getCurrentStep(): int
    {
        if ($this->email_sent) return 4;
        if ($this->is_delivered) return 3;
        if ($this->is_sending) return 2;
        return 1; // preparing
    }

    /**
     * Get the current step name
     */
    public function getCurrentStepName(): string
    {
        $steps = [
            1 => 'Preparing Fax',
            2 => 'Sending',
            3 => 'Delivered',
            4 => 'Email Confirmation'
        ];
        
        return $steps[$this->getCurrentStep()];
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage(): int
    {
        return ($this->getCurrentStep() / 4) * 100;
    }

    /**
     * Mark as sending started
     */
    public function markSendingStarted()
    {
        $this->update([
            'is_preparing' => false,
            'is_sending' => true,
            'prepared_at' => $this->prepared_at ?? now(),
            'sending_started_at' => now()
        ]);
    }

    /**
     * Mark as delivered
     */
    public function markDelivered($telnyxStatus = null, $deliveryDetails = null)
    {
        $this->update([
            'is_sending' => false,
            'is_delivered' => true,
            'delivered_at' => now(),
            'telnyx_status' => $telnyxStatus,
            'delivery_details' => $deliveryDetails
        ]);
    }

    /**
     * Mark email as sent
     */
    public function markEmailSent()
    {
        $this->update([
            'email_sent' => true,
            'email_sent_at' => now()
        ]);
    }

    /**
     * Mark reminder email as sent
     */
    public function markReminderEmailSent()
    {
        $this->update([
            'reminder_email_sent' => true,
            'reminder_email_sent_at' => now()
        ]);
    }

    /**
     * Mark early reminder email as sent
     */
    public function markEarlyReminderEmailSent()
    {
        $this->update([
            'early_reminder_sent' => true,
            'early_reminder_sent_at' => now()
        ]);
    }

    /**
     * Apply discount to this fax job
     */
    public function applyDiscount($discountCode, $discountAmount)
    {
        $this->update([
            'original_amount' => $this->amount,
            'discount_code' => $discountCode,
            'discount_amount' => $discountAmount,
            'amount' => max(0, $this->amount - $discountAmount)
        ]);
    }

    /**
     * Check if this fax job has a discount applied
     */
    public function hasDiscount(): bool
    {
        return !empty($this->discount_code) && $this->discount_amount > 0;
    }

    /**
     * Get the final amount after discount
     */
    public function getFinalAmount(): float
    {
        return $this->hasDiscount() ? max(0, ($this->original_amount ?? $this->amount) - $this->discount_amount) : $this->amount;
    }

    /**
     * Track email click with type and UTM parameters
     */
    public function trackEmailClick($emailType, $utmParams = [])
    {
        $clickData = [
            'type' => $emailType,
            'clicked_at' => now()->toISOString(),
            'utm_source' => $utmParams['utm_source'] ?? null,
            'utm_medium' => $utmParams['utm_medium'] ?? null,
            'utm_campaign' => $utmParams['utm_campaign'] ?? null,
        ];

        $existingClicks = $this->email_clicks ?? [];
        $existingClicks[] = $clickData;

        $updateData = [
            'email_click_count' => $this->email_click_count + 1,
            'email_clicks' => $existingClicks,
            'last_utm_source' => $utmParams['utm_source'] ?? null,
            'last_utm_medium' => $utmParams['utm_medium'] ?? null,
            'last_utm_campaign' => $utmParams['utm_campaign'] ?? null,
        ];

        // Update specific timestamp field based on email type
        if ($emailType === 'early_reminder') {
            $updateData['early_reminder_clicked_at'] = now();
        } elseif ($emailType === 'reminder') {
            $updateData['reminder_clicked_at'] = now();
        }

        $this->update($updateData);
    }

    /**
     * Get email click statistics
     */
    public function getEmailClickStats()
    {
        $clicks = $this->email_clicks ?? [];
        $stats = [
            'total_clicks' => $this->email_click_count,
            'early_reminder_clicks' => 0,
            'reminder_clicks' => 0,
            'first_click_at' => null,
            'last_click_at' => null,
        ];

        foreach ($clicks as $click) {
            if ($click['type'] === 'early_reminder') {
                $stats['early_reminder_clicks']++;
            } elseif ($click['type'] === 'reminder') {
                $stats['reminder_clicks']++;
            }

            if ($stats['first_click_at'] === null || $click['clicked_at'] < $stats['first_click_at']) {
                $stats['first_click_at'] = $click['clicked_at'];
            }
            if ($stats['last_click_at'] === null || $click['clicked_at'] > $stats['last_click_at']) {
                $stats['last_click_at'] = $click['clicked_at'];
            }
        }

        return $stats;
    }

    /**
     * Mark failure notification email as sent
     */
    public function markFailureEmailSent()
    {
        $this->update([
            'failure_email_sent' => true,
        ]);
    }

    /**
     * Check if this fax job should receive a reminder email
     */
    public function shouldReceiveReminder($hoursThreshold = 24): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAYMENT_PENDING])
            && $this->created_at <= now()->subHours($hoursThreshold)
            && !empty($this->sender_email)
            && !$this->reminder_email_sent;
    }

    /**
     * Check if this fax job should receive an early reminder email
     */
    public function shouldReceiveEarlyReminder($hoursThreshold = 2): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PAYMENT_PENDING])
            && $this->created_at <= now()->subHours($hoursThreshold)
            && !empty($this->sender_email)
            && !$this->early_reminder_sent;
    }

    /**
     * Check if this fax job failed due to ECM compatibility issues
     */
    public function hasEcmError(): bool
    {
        if ($this->status !== self::STATUS_FAILED || empty($this->error_message)) {
            return false;
        }

        return str_contains(strtolower($this->error_message), 'ecm') || 
               str_contains(strtolower($this->error_message), 'error_correction') ||
               str_contains(strtolower($this->error_message), 'invalid_ecm_response');
    }

    /**
     * Get user-friendly explanation for ECM errors
     */
    public function getEcmErrorExplanation(): ?string
    {
        if (!$this->hasEcmError()) {
            return null;
        }

        return "ECM (Error Correction Mode) compatibility issue with the receiving fax machine. " .
               "Ask the recipient to disable ECM on their fax machine and try again.";
    }

    /**
     * Check if this conversion came from AdWords
     */
    public function isAdWordsConversion(): bool
    {
        return $this->traffic_source === 'adwords';
    }

    /**
     * Check if this conversion came from organic search
     */
    public function isOrganicConversion(): bool
    {
        return $this->traffic_source === 'organic';
    }

    /**
     * Get formatted traffic source display name
     */
    public function getTrafficSourceDisplayName(): string
    {
        return match($this->traffic_source) {
            'adwords' => 'Google Ads',
            'organic' => 'Organic Search',
            'direct' => 'Direct Traffic',
            'referral' => 'Referral',
            'social' => 'Social Media',
            'email' => 'Email Marketing',
            'paid' => 'Paid Advertising',
            'campaign' => 'Campaign Traffic',
            default => $this->traffic_source ? ucfirst($this->traffic_source) : 'Unknown'
        };
    }

    /**
     * Get AdWords campaign name if available
     */
    public function getAdWordsCampaign(): ?string
    {
        return $this->isAdWordsConversion() ? $this->utm_campaign : null;
    }

    /**
     * Get AdWords keyword if available
     */
    public function getAdWordsKeyword(): ?string
    {
        return $this->isAdWordsConversion() ? $this->utm_term : null;
    }
}
