<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Person extends Model
{
    protected $casts = [
        'state_born_in' => 'string',
        'state_parents_born_in' => 'json',
        'favorite_coffee' => 'json'
    ];

    public function favoriteState(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function statesLivedIn(): BelongsToMany
    {
        return $this->belongsToMany(State::class, 'state_user_lived_in')
            ->withTimestamps();
    }

    public function statesVisited(): BelongsToMany
    {
        return $this->belongsToMany(State::class, 'state_user_visited')
            ->withPivot('order')
            ->orderBy('order')
            ->withTimestamps();
    }
}
