<?php

namespace App\Models;

use App\Models\Concerns\FiltersByMonth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketExpense extends Model
{
    use FiltersByMonth;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'amount',
        'date',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
