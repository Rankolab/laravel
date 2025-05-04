<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RssFeed extends Model
{
    use HasFactory;

    protected $primaryKey = 'feed_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'feed_url',
        'feed_name',
        'quantity',
        'word_count',
        'schedule',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'schedule' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the website that owns the RSS feed.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
