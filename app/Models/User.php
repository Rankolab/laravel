<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // Import HasApiTokens

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // Use HasApiTokens

    protected $primaryKey = 'user_id'; // Set custom primary key
    public $incrementing = true; // Ensure primary key is auto-incrementing
    protected $keyType = 'int'; // Ensure primary key type is integer

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password_hash', // Use password_hash to match migration
        'license_key',
        'license_status',
        'license_type',
        'license_start_date',
        'license_end_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash', // Hide password_hash
        // 'remember_token', // Default Laravel field, not in our migration
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // 'email_verified_at' => 'datetime', // Default Laravel field, not in our migration
            'password_hash' => 'hashed', // Use password_hash for casting
            'license_start_date' => 'datetime',
            'license_end_date' => 'datetime',
        ];
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash; // Tell Laravel to use password_hash for authentication
    }
}
