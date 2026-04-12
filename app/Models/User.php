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

    public const ROLE_ADMIN = 'admin';

    public const ROLE_USER = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'account_role',
        'google_id',
        'google_avatar',
        'avatar_path',
        'phone',
        'profile_role',
        'profile_location',
        'bio',
        'country',
        'city_state',
        'postal_code',
        'tax_id',
        'facebook_url',
        'x_url',
        'linkedin_url',
        'instagram_url',
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
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->account_role === self::ROLE_ADMIN;
    }

    public function isPrimaryAdmin(): bool
    {
        $primaryAdminEmail = config('admin.email');

        return is_string($primaryAdminEmail)
            && $primaryAdminEmail !== ''
            && strcasecmp((string) $this->email, $primaryAdminEmail) === 0;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_path && $this->exists) {
            return route('users.avatar', $this);
        }

        return $this->google_avatar;
    }
}
