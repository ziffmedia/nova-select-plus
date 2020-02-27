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
