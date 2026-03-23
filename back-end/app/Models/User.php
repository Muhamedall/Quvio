<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
#[Fillable(['name', 'email', 'password' ,'branding'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'branding'  => 'array'
        ];
    }

    // ── RELATIONSHIPS ─────────────────────────────────────
    // These are the methods Laravel calls when you do:
    // auth()->user()->clients()
    // auth()->user()->quotes()
    // auth()->user()->invoices()

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

     public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class);
    }

     public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

     // ── ACCESSOR ──────────────────────────────────────────
    protected function initials(): Attribute
    {
        return Attribute::make(
            get: fn (): string => strtoupper(substr($this->name, 0, 1))
        );
    }
 


}
