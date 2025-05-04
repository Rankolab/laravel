<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id'; // Set custom primary key
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'website_id',
        'user_id',
        'session_id',
        'user_message',
        'bot_response',
        'timestamp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Get the website associated with the chatbot log.
     */
    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    /**
     * Get the user associated with the chatbot log (optional).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
