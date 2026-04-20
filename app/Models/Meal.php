<?php

namespace App\Models;

use App\Models\Concerns\FiltersByMonth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meal extends Model
{
    use FiltersByMonth;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'breakfast',
        'lunch',
        'dinner',
        'guest_meals',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'lunch' => 'boolean',
            'dinner' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
