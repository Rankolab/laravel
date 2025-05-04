<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteMetric extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'website_metrics';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'website_metric_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Assuming created_at and updated_at exist based on migration

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'page_speed_score',
        'seo_score',
        'domain_authority',
        'backlinks_count',
        'last_analyzed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_analyzed' => 'datetime',
        'page_speed_score' => 'integer',
        'seo_score' => 'integer',
        'domain_authority' => 'integer',
        'backlinks_count' => 'integer',
    ];

    /**
     * Get the website that owns the metrics.
     */
    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class, 'website_id', 'website_id');
    }
}

