<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'fax_credits',
        'credits_purchased_at',
        'stripe_customer_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'credits_purchased_at' => 'datetime',
        ];
    }

    /**
     * Check if user has available fax credits
     */
    public function hasCredits(): bool
    {
        return $this->fax_credits > 0;
    }

    /**
     * Deduct one fax credit
     */
    public function deductCredit(): bool
    {
        if ($this->fax_credits > 0) {
            $this->decrement('fax_credits');
            return true;
        }
        return false;
    }

    /**
     * Add fax credits to account
     */
    public function addCredits(int $amount): void
    {
        $this->increment('fax_credits', $amount);
        if ($this->credits_purchased_at === null) {
            $this->update(['credits_purchased_at' => now()]);
        }
    }

    /**
     * Get all fax jobs sent by this user
     */
    public function faxJobs()
    {
        return $this->hasMany(\App\Models\FaxJob::class, 'sender_email', 'email');
    }


}
