<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shelter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'phone',
        'uid',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }
}
