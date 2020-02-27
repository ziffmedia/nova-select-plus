# A Nova BelongsToMany Mulitselect Thing

## Installation

```
composer require
```

## Usage

```
RelationMultiselect::make('Authors')
    ->usingIndexLabel('name')      // index screen: the attribute you want to use as a label (can be real or dynamic)
    ->usingDetailLabel('name')     // detail screen: '''
    ->usingFormLabel('first_name') // forms: the attribute to use as the label of the selection, defaults to `name`
```

## Todo

### Immediate term

- rename: "one true multiselect?"
- Static list of options via ->options()
- Static list of options stored in a json/array column
- Reordering of Selected options
- ajax *search* for multiselect

#### Research

- repositioning of the dropdown? (another library does this)
- support ordered list of other models ids in a json/array/collection field?

