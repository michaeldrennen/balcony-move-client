<?php

declare(strict_types=1);

namespace MichaelDrennen\Balcony\MoveClient\Models;

use Illuminate\Database\Eloquent\Model;

final class MoveIndexObservation extends Model
{
    protected $table = 'balcony_move_index_observations';

    protected $guarded = ['id'];

    protected $casts = [
        'observed_at' => 'date',
        'close_value' => 'decimal:4',
        'day_change' => 'decimal:4',
    ];
}
