<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class EmailVerification extends Model
{
    protected $fillable = ['email', 'code_hash', 'verified', 'expires_at'];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function matches(string $code): bool
    {
        return Hash::check($code, $this->code_hash);
    }
}
