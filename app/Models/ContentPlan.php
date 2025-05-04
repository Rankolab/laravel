<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPlan extends Model
{
    use HasFactory;

    protected $primaryKey = 'plan_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'keywords',
        'competitor_urls',
        'content_types',
        'volume',
        'schedule',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'keywords' => 'array',
        'competitor_urls' => 'array',
        'content_types' => 'array',
        'schedule' => 'array',
    ];

    /**
     * Get the website that owns the content plan.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
