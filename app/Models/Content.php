<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory;

    protected $table = 'content'; // Explicitly define table name if it differs from pluralized model name
    protected $primaryKey = 'content_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'plan_id',
        'title',
        'body',
        'word_count',
        'featured_image_url',
        'images',
        'internal_links',
        'external_links',
        'affiliate_links',
        'keywords',
        'status',
        'published_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'images' => 'array',
        'internal_links' => 'array',
        'external_links' => 'array',
        'affiliate_links' => 'array',
        'keywords' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Get the website that owns the content.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    /**
     * Get the content plan associated with the content (optional).
     */
    public function contentPlan()
    {
        return $this->belongsTo(ContentPlan::class, 'plan_id');
    }
}
