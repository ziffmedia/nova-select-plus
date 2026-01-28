# Dependent Fields

SelectPlus fully supports Nova's dependent fields functionality, allowing you to dynamically modify field behavior based on the values of other fields in the form.

## Basic Usage

Use the `dependsOn` method to create a dependent field. The method accepts an array of field attributes to depend on and a callback function that receives the field instance, request, and form data.

```php
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use ZiffMedia\NovaSelectPlus\SelectPlus;

// Basic dependent field example
Select::make('Region', 'region')
    ->options([
        'west' => 'West Coast',
        'east' => 'East Coast',
        'central' => 'Central',
    ]),

SelectPlus::make('Available States', 'available_states')
    ->options(State::class)
    ->dependsOn(['region'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        if ($formData->region === 'west') {
            $field->optionsQuery(function ($query) {
                $query->whereIn('code', ['CA', 'WA', 'OR']);
            });
        } elseif ($formData->region === 'east') {
            $field->optionsQuery(function ($query) {
                $query->whereIn('code', ['NY', 'FL', 'MA']);
            });
        }
    })
```

## Callback Parameters

The dependent field callback receives three parameters:

1. **`SelectPlus $field`** - The field instance that can be modified
2. **`NovaRequest $request`** - The current Nova request with access to resource context
3. **`FormData $formData`** - Object containing all current form field values

```php
SelectPlus::make('Cities', 'cities')
    ->options(City::class)
    ->dependsOn(['region', 'state_id'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        $region = $formData->region;
        $stateId = $formData->state_id;
        
        // Access request context
        $resourceId = $request->get('resourceId');
        
        // Modify field based on dependent values
        if ($region && $stateId) {
            $field->optionsQuery(function ($query) use ($region, $stateId) {
                $query->whereHas('state', function ($q) use ($stateId) {
                    $q->where('id', $stateId);
                });
            });
            
            $field->help("Showing cities in {$region} region for selected state");
        }
    })
```

## Multiple Dependencies

A SelectPlus field can depend on multiple other fields by passing multiple attribute names to the `dependsOn` method:

```php
SelectPlus::make('Products', 'products')
    ->options(Product::class)
    ->dependsOn(['category', 'price_range', 'brand'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        $category = $formData->category;
        $priceRange = $formData->price_range;
        $brand = $formData->brand;
        
        $field->optionsQuery(function ($query) use ($category, $priceRange, $brand) {
            if ($category) {
                $query->where('category_id', $category);
            }
            
            if ($priceRange) {
                [$min, $max] = explode('-', $priceRange);
                $query->whereBetween('price', [$min, $max]);
            }
            
            if ($brand) {
                $query->where('brand_id', $brand);
            }
        });
    })
```

## Common Use Cases

### Filtering Options

Filter available options based on another field's value:

```php
SelectPlus::make('States Lived In', 'states_lived_in')
    ->options(State::class)
    ->dependsOn(['only_certain_states'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        if ($formData->only_certain_states === 'Yes') {
            $field->optionsQuery(function ($query) {
                $query->where('name', 'LIKE', 'L%');
            });
        }
    })
```

### Dynamic Help Text

Update help text based on dependent field values:

```php
SelectPlus::make('Available Options', 'options')
    ->options(Option::class)
    ->dependsOn(['filter_type'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        $filterType = $formData->filter_type;
        
        if ($filterType === 'premium') {
            $field->optionsQuery(function ($query) {
                $query->where('is_premium', true);
            });
            $field->help('Showing premium options only');
        } else {
            $field->help('Showing all available options');
        }
    })
```

### Conditional Ajax Searchable

Modify ajax search behavior based on dependent fields:

```php
SelectPlus::make('Searchable Items', 'items')
    ->options(Item::class)
    ->ajaxSearchable(true)
    ->dependsOn(['search_mode'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        if ($formData->search_mode === 'strict') {
            $field->ajaxSearchable(function ($query, $search) {
                $query->where('name', '=', $search);
            });
        } else {
            $field->ajaxSearchable(function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%");
            });
        }
    })
```

### Conditional Validation

Modify field validation rules based on dependent values:

```php
SelectPlus::make('Required Field', 'required_field')
    ->options(['option1', 'option2', 'option3'])
    ->dependsOn(['make_required'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        if ($formData->make_required === true) {
            $field->rules('required');
            $field->help('This field is now required');
        } else {
            $field->rules('nullable');
            $field->help('This field is optional');
        }
    })
```

## Working with Ajax Searchable Fields

Dependent fields work seamlessly with ajax searchable SelectPlus fields. The dependent field logic is applied when the options are loaded via AJAX:

```php
SelectPlus::make('Searchable Cities', 'cities')
    ->options(City::class)
    ->ajaxSearchable(true)
    ->dependsOn(['country'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
        if ($formData->country) {
            $field->optionsQuery(function ($query) use ($formData) {
                $query->where('country_id', $formData->country);
            });
        }
    })
```

## Frontend Integration

The SelectPlus frontend component automatically handles dependent field changes:

- When a dependent field value changes, the SelectPlus field's options are automatically refreshed
- The dependent field values are passed to the backend via the `dependsOn` parameter
- The callback is executed on the server side to modify the field configuration
- The updated options are returned and displayed in the SelectPlus field

## Best Practices

1. **Keep callbacks lightweight** - Avoid heavy computations in dependent field callbacks
2. **Use optionsQuery for filtering** - Prefer `optionsQuery()` over modifying the base `options()` for better performance
3. **Provide user feedback** - Use `help()` text to inform users about filtering or changes
4. **Handle null values** - Always check for null/empty dependent field values
5. **Test thoroughly** - Test all combinations of dependent field values

## Limitations

- Dependent fields cannot depend on fields that don't live-report their changes (see Nova documentation)
- Complex nested dependencies should be avoided for performance reasons
- The callback is executed on every options request, so keep it efficient

## Example: Complete Implementation

Here's a complete example showing a dependent SelectPlus field in a Nova resource:

```php
<?php

namespace App\Nova;

use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class Person extends Resource
{
    public function fields(NovaRequest $request)
    {
        return [
            Select::make('Region', 'region')
                ->options([
                    'west' => 'West Coast',
                    'east' => 'East Coast',
                    'central' => 'Central',
                ])
                ->help('Select a region to filter available states'),

            SelectPlus::make('States Visited', 'states_visited')
                ->options(State::class)
                ->dependsOn(['region'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                    $region = $formData->region;
                    
                    if ($region === 'west') {
                        $field->optionsQuery(function ($query) {
                            $query->whereIn('code', ['CA', 'WA', 'OR']);
                        });
                        $field->help('Showing West Coast states');
                    } elseif ($region === 'east') {
                        $field->optionsQuery(function ($query) {
                            $query->whereIn('code', ['NY', 'FL', 'MA']);
                        });
                        $field->help('Showing East Coast states');
                    } else {
                        $field->help('Select a region to filter states');
                    }
                })
                ->ajaxSearchable(true)
                ->help('This field depends on the selected region'),
        ];
    }
}
```

This implementation provides full support for Nova's dependent fields functionality, allowing SelectPlus fields to dynamically respond to changes in other form fields with both Request and FormData objects available in the callback.