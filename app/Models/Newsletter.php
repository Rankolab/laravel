<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    use HasFactory;

    protected $primaryKey = 'newsletter_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "user_id", // Added user_id
        "website_id",
        "subject",
        "content", // Changed from body to content based on migration
        "status",
        "scheduled_at",
        "sent_at",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the website associated with the newsletter.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
