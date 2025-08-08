<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AccountAccessToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Generate a new secure token for email access
     */
    public static function createForEmail(string $email): string
    {
        // Clean up old expired tokens for this email
        static::where('email', $email)
            ->where('expires_at', '<', now())
            ->delete();

        // Generate a cryptographically secure token
        $token = Str::random(64);

        // Create the token record (expires in 1 hour)
        static::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => now()->addHour(),
        ]);

        return $token;
    }

    /**
     * Verify and consume a token
     */
    public static function verifyAndConsume(string $token): ?string
    {
        $tokenRecord = static::where('token', $token)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (!$tokenRecord) {
            return null;
        }

        // Mark token as used
        $tokenRecord->update(['used_at' => now()]);

        return $tokenRecord->email;
    }

    /**
     * Check if token is valid (not expired, not used)
     */
    public function isValid(): bool
    {
        return $this->expires_at > now() && $this->used_at === null;
    }

    /**
     * Cleanup expired tokens (can be run via scheduled job)
     */
    public static function cleanupExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }
}