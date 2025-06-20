<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FaxJob extends Model
{
    protected $fillable = [
        'recipient_number',
        'sender_name',
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
        'is_compressed',
        'original_file_size',
        'compressed_file_size',
        'compression_ratio',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'last_retry_at' => 'datetime',
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
}
