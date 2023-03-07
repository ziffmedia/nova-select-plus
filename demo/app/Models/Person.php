<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    public function statesLivedIn()
    {
        return $this->belongsToMany(State::class, 'state_user_lived_in')
            ->withTimestamps();
    }

    public function statesVisited()
    {
        return $this->belongsToMany(State::class, 'state_user_visited')
            ->withPivot('order')
            ->orderBy('order')
            ->withTimestamps();
    }
}
