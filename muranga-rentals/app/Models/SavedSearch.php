<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'search_criteria',
        'alert_enabled',
        'alert_frequency',
        'last_alert_sent',
    ];

    protected function casts(): array
    {
        return [
            'search_criteria' => 'array',
            'alert_enabled' => 'boolean',
            'last_alert_sent' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Made with Bob
