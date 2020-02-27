# Nova Select Plus

## Installation

```
composer require ziffmedia/nova-select-plus
```

## Usage

```php
RelationMultiselect::make('Authors')
    ->usingIndexLabel('name')      // index screen: the attribute you want to use as a label (can be real or dynamic)
    ->usingDetailLabel('name')     // detail screen: '''
    ->usingFormLabel('first_name') // forms: the attribute to use as the label of the selection, defaults to `name`
```

## Todo

### Immediate term

- ajax *search* for multiselect
- Static list of options via ->options()
- Static list of options stored in a json/array column
- Reordering of Selected options


#### Research

- repositioning of the dropdown? (another library does this)
- support ordered list of other models ids in a json/array/collection field?

