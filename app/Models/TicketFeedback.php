<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TicketFeedback extends Model
{
    protected $table = 'ticket_feedbacks';

    protected $fillable = [
        'feedbackable_type',
        'feedbackable_id',
        'rating',
        'komentar',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function feedbackable(): MorphTo
    {
        return $this->morphTo();
    }
}
