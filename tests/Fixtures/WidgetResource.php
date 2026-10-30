<?php

namespace Tests\Fixtures;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class WidgetResource extends Resource
{
    public static $model = Widget::class;

    public static $title = 'name';

    public static bool $denyViewAny = false;

    public static function uriKey()
    {
        return 'widgets';
    }

    public static function authorizedToViewAny(Request $request)
    {
        return ! static::$denyViewAny;
    }

    public function fields(NovaRequest $request)
    {
        return [
            ID::make(),

            // options come from a relationship
            SelectPlus::make('Gadgets', 'gadgets')->label('name'),

            // options come from a model class
            SelectPlus::make('Other Gadgets', 'other_gadgets')->options(Gadget::class)->label('name'),

            // options come from an array
            SelectPlus::make('Colors', 'colors')->options(['red' => 'Red', 'blue' => 'Blue']),

            // options come from a field that the user may not see
            SelectPlus::make('Hidden', 'hidden')->options(['a' => 'A'])->canSee(fn () => false),

            // reachable only by field id, which skips the field lookup by attribute
            SelectPlus::make('Identified', 'identified')->options(['x' => 'X'])->withFieldId('identified-field')->canSee(fn () => false),
        ];
    }
}
