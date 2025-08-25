<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaxJob extends Model
{
    protected $fillable = [
        'hash',
        'recipient_number',
        'sender_email',
        'file_path',
        'file_original_name',
        'amount',
        'payment_intent_id',
        'status',
        'telnyx_fax_id',
        'retry_attempts',
        'last_retry_at',
        'error_message',

        'original_file_size',
        'is_preparing',
        'is_sending',
        'is_delivered',
        'email_sent',
        'reminder_email_sent',
        'prepared_at',
        'sending_started_at',
        'delivered_at',
        'email_sent_at',
        'reminder_email_sent_at',
        'delivery_details',
        'telnyx_status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'last_retry_at' => 'datetime',
        'prepared_at' => 'datetime',
        'sending_started_at' => 'datetime',
        'delivered_at' => 'datetime',
        'email_sent_at' => 'datetime',
        'reminder_email_sent_at' => 'datetime',
        'is_preparing' => 'boolean',
        'is_sending' => 'boolean',
        'is_delivered' => 'boolean',
        'email_sent' => 'boolean',
        'reminder_email_sent' => 'boolean',
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
}
