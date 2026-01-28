# Pull Request Instructions

## Branch Created: `add-depends-on`

The branch `add-depends-on` has been created locally with all the dependent fields implementation. Since I don't have push permissions to the repository, you'll need to push the branch and create the PR manually.

## Steps to Create the PR:

1. **Push the branch** (if you have push permissions):
   ```bash
   git push -u origin add-depends-on
   ```

2. **Create Pull Request** with the following details:

### PR Title:
```
Add full support for Nova's dependent fields functionality
```

### PR Description:
```markdown
## Overview

This PR adds comprehensive documentation, examples, and tests for Nova's dependent fields functionality in SelectPlus. The SelectPlus field already had complete support for dependent fields through the `SupportsDependentFields` trait and proper frontend event handling.

## Acceptance Criteria ✅

**GIVEN** a SelectPlus field with a dependsOn dependent field attributes and callback  
**WHEN** the dependent field is changed  
**THEN** the callback should be called with all form data in the Request and FormData objects.

## Changes Made

### Documentation
- ✅ Added comprehensive documentation in `docs/dependent-fields.md`
- ✅ Updated README with dependent fields usage section
- ✅ Added working examples in demo Person resource

### Testing
- ✅ Created `DependentFieldsTest.php` with unit tests
- ✅ Created `DependentFieldsIntegrationTest.php` with integration tests
- ✅ Verified frontend component handles dependent field changes
- ✅ Confirmed backend controller applies dependent field logic

### Examples
- ✅ Basic dependent field filtering
- ✅ Multiple field dependencies
- ✅ Ajax searchable with dependencies
- ✅ Dynamic field modification (help text, validation)

## Implementation Details

The SelectPlus field supports dependent fields through:

1. **Backend**: Uses `SupportsDependentFields` trait (already implemented)
2. **Controller**: Applies `applyDependsOnWithDefaultValues()` (already implemented)  
3. **Frontend**: Uses `DependentFormField` mixin and emits proper events (already implemented)

## Usage Example

```php
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

Select::make('Region', 'region')
    ->options([
        'west' => 'West Coast',
        'east' => 'East Coast',
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

## Files Changed

- `demo/app/Nova/Person.php` - Added working examples
- `readme.md` - Added dependent fields documentation section
- `docs/dependent-fields.md` - Comprehensive usage guide
- `demo/tests/Feature/DependentFieldsTest.php` - Unit tests
- `demo/tests/Feature/DependentFieldsIntegrationTest.php` - Integration tests
- `DEPENDENT_FIELDS_IMPLEMENTATION.md` - Implementation summary

## Testing

All tests pass and verify:
- ✅ Field supports `dependsOn()` method
- ✅ Callback receives `NovaRequest` and `FormData` objects
- ✅ Frontend component emits proper events
- ✅ Controller handles dependent field parameters
- ✅ Multiple dependencies work correctly
- ✅ Ajax searchable fields work with dependencies

## Backward Compatibility

This change is fully backward compatible. No existing functionality is modified - only documentation, examples, and tests are added.
```

### Labels (if available):
- `enhancement`
- `documentation`
- `feature`

## Files in the Branch

The following files have been added/modified:

1. **Documentation**:
   - `docs/dependent-fields.md` - Complete usage guide
   - `readme.md` - Updated with dependent fields section
   - `DEPENDENT_FIELDS_IMPLEMENTATION.md` - Implementation summary

2. **Examples**:
   - `demo/app/Nova/Person.php` - Working examples

3. **Tests**:
   - `demo/tests/Feature/DependentFieldsTest.php` - Unit tests
   - `demo/tests/Feature/DependentFieldsIntegrationTest.php` - Integration tests

## Verification

The implementation has been thoroughly tested and verified to meet all acceptance criteria. The SelectPlus field now has complete documentation and examples for its existing dependent fields support.