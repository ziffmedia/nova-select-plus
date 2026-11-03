<?php

namespace Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Widget extends Model
{
    protected $guarded = [];

    public function gadgets(): HasMany
    {
        return $this->hasMany(Gadget::class);
    }
}
