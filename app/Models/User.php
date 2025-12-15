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
        'password',
        'role',
        'is_admin',  // keep for backward compatibility
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
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
            'password'          => 'hashed',
            'is_admin'          => 'boolean',
        ];
    }

    /* ===========================
     *  Role helpers
     * =========================== */

    public function isAdmin(): bool
    {
        // Treat either explicit role OR old is_admin flag as admin
        return $this->role === 'admin' || (bool) $this->is_admin;
    }

    public function isConsultant(): bool
    {
        // adjust depending on how you store consultant role
        // Option 1: boolean column is_consultant
        $byFlag = (bool) ($this->is_consultant ?? false);

        // Option 2: role column == 'consultant'
        $byRole = isset($this->role) && $this->role === 'consultant';

        return $byFlag || $byRole;
    }

    public function isNormalUser(): bool
    {
        // default user role
        return $this->role === 'user' || (!$this->role && !$this->is_admin);
    }
}
