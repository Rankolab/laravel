<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    use HasFactory;

    protected $primaryKey = 'website_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'domain',
        'niche',
        'website_type',
    ];

    /**
     * Get the user that owns the website.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Define relationships for metrics, designs, content plans, etc. later as needed
    public function metrics()
    {
        return $this->hasOne(WebsiteMetric::class, 'website_id'); // Assuming WebsiteMetric model exists
    }
}
