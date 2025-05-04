<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebsiteDesign extends Model
{
    use HasFactory;

    protected $primaryKey = 'design_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'template_name',
        'color_scheme',
        'typography',
        'layout',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'color_scheme' => 'array', // Cast JSON fields to array
        'typography' => 'array',
    ];

    /**
     * Get the website that owns the design.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
