# Nova Select Plus

## Installation

```
composer require ziffmedia/nova-select-plus
```

## Description & Use Cases

This Nova component was built to satisfy the use cases just beyond Nova's built-in <select> component. Here are
some scenarios where you might want `SelectPlus` (which uses `vue-select`) over the simple `Select`:

#### Select For BelongsToMany and MorphsToMany On the Form Screen

The default Nova experience for `BelongsToMany` and `MorphsToMany` is to have a separate UI screen for
attaching/detaching and syncing relationships through a "Pivot" model. For simple relationships (relaionships that do
not have addition pivot values or the only value in the pivot table is there for ordering), it is benefitial to move
this Selection to the Form workflow instead of a separate workflow.

#### Ajax For Options

For Select's that have between a handful to several 1000 options, it is more peformant to load the full list of options
only on the screen that needs it: the Form screen.

There are 2 options for Ajax Options, the default is to load them all on the Form load. The second is to allow for full
option searching (in this case you can write you own ajax search resolver).

### Reordering Simple Pivot/BelongsToMany Relations

Through `->reorderable()`, you can enable a `SelectPlus` field to be reorderable. This allows, at `BelongsToMany->sync()`
time, to populate a pivot value useful for ordering relations.

## Usage

```php
SelectPlus::make('Authors')

SelectPlus::make('Favorite Books', 'favoriteBooks', Books::class) // including the relation method & Nova Resource for relation
```

### Options

| Method | Description |
|--------|-------------|
| `->label($attribute = 'name')` | Default `name` |
| `->usingIndexLabel($attribute|closure)` | Supply an `$attribute` that will be used to pluck from models and comma separate. Supply a closure to return either a string or array of strings |
| `->usingDetailLabel($attribute|closure))` | |
| `->ajaxSearchable(callable)` | |
| `->maxSelections(integer)` | |
| `->reorderable()` | |

