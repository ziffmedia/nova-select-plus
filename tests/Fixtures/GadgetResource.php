<?php

namespace Tests\Fixtures;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class GadgetResource extends Resource
{
    public static $model = Gadget::class;

    public static $title = 'name';

    public static bool $denyViewAny = false;

    public static function uriKey()
    {
        return 'gadgets';
    }

    public static function authorizedToViewAny(Request $request)
    {
        return ! static::$denyViewAny;
    }

    public function fields(NovaRequest $request)
    {
        return [ID::make()];
    }
}
