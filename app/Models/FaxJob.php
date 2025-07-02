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
        'prepared_at',
        'sending_started_at',
        'delivered_at',
        'email_sent_at',
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
        'is_preparing' => 'boolean',
        'is_sending' => 'boolean',
        'is_delivered' => 'boolean',
        'email_sent' => 'boolean',
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
        return $this->retry_attempts < 2;
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
}
