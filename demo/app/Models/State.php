<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class State extends Model
{
    protected $fillable = ['name', 'code'];

    public function capitalCity(): HasOne
    {
        return $this->hasOne(City::class);
    }

    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}
