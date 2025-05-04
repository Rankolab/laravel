<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceMetric extends Model
{
    use HasFactory;

    protected $primaryKey = 'performance_id'; // Set custom primary key
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
        'keyword',
        'ranking',
        'clicks',
        'impressions',
        'affiliate_clicks',
        'affiliate_earnings',
        'indexed_status',
        'last_checked',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'affiliate_earnings' => 'decimal:2',
        'last_checked' => 'datetime',
    ];

    /**
     * Get the website associated with the performance metric.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    /**
     * Get the content associated with the performance metric (optional).
     */
    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
