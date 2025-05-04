<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMediaPost extends Model
{
    use HasFactory;

    protected $primaryKey = 'post_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'content_id',
        'platform',
        'post_content',
        'post_url',
        'status',
        'scheduled_at',
        'posted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'posted_at' => 'datetime',
    ];

    /**
     * Get the website associated with the social media post.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    /**
     * Get the content associated with the social media post (optional).
     */
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
