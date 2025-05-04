<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinkBuilding extends Model
{
    use HasFactory;

    protected $table = 'link_building'; // Explicitly define table name
    protected $primaryKey = 'link_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'target_url',
        'source_url',
        'anchor_text',
        'status',
        'link_type',
        'acquired_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'acquired_date' => 'date',
    ];

    /**
     * Get the website associated with the link building record.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }
}
