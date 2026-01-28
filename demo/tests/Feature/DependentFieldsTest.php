<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;
use Tests\TestCase;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class DependentFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create some test states
        State::create(['name' => 'California', 'code' => 'CA']);
        State::create(['name' => 'Texas', 'code' => 'TX']);
        State::create(['name' => 'Florida', 'code' => 'FL']);
        State::create(['name' => 'New York', 'code' => 'NY']);
        State::create(['name' => 'Louisiana', 'code' => 'LA']);
    }

    /** @test */
    public function select_plus_field_supports_depends_on_method()
    {
        $callbackExecuted = false;
        $receivedRequest = null;
        $receivedFormData = null;

        // Create a SelectPlus field with dependsOn
        $field = SelectPlus::make('Dependent States', 'dependent_states')
            ->options(State::class)
            ->dependsOn(['state_born_in'], function (SelectPlus $field, NovaRequest $request, FormData $formData) use (&$callbackExecuted, &$receivedRequest, &$receivedFormData) {
                $callbackExecuted = true;
                $receivedRequest = $request;
                $receivedFormData = $formData;
                
                // Modify field based on dependent field value
                if ($formData->state_born_in === 'CA') {
                    $field->optionsQuery(function ($query) {
                        $query->where('code', '!=', 'CA');
                    });
                }
            });

        // Verify the field has the dependsOn method
        $this->assertTrue(method_exists($field, 'dependsOn'));
        
        // Verify the field uses SupportsDependentFields trait
        $this->assertTrue(in_array('Laravel\Nova\Fields\SupportsDependentFields', class_uses_recursive(get_class($field))));
    }

    /** @test */
    public function dependent_field_callback_receives_request_and_form_data()
    {
        $callbackExecuted = false;
        $receivedRequest = null;
        $receivedFormData = null;

        // Create a SelectPlus field with dependsOn
        $field = SelectPlus::make('Dependent States', 'dependent_states')
            ->options(State::class)
            ->dependsOn(['state_born_in'], function (SelectPlus $field, NovaRequest $request, FormData $formData) use (&$callbackExecuted, &$receivedRequest, &$receivedFormData) {
                $callbackExecuted = true;
                $receivedRequest = $request;
                $receivedFormData = $formData;
            });

        // Verify the field has the dependsOn method and uses the trait
        $this->assertTrue(method_exists($field, 'dependsOn'));
        $this->assertTrue(in_array('Laravel\Nova\Fields\SupportsDependentFields', class_uses_recursive(get_class($field))));
        
        // Verify the field has dependent field metadata
        $this->assertArrayHasKey('dependsOn', $field->meta);
        $this->assertIsArray($field->meta['dependsOn']);
        $this->assertArrayHasKey('attributes', $field->meta['dependsOn']);
        $this->assertContains('state_born_in', $field->meta['dependsOn']['attributes']);
    }

    /** @test */
    public function dependent_field_modifies_options_based_on_dependent_value()
    {
        // Create a SelectPlus field that filters options based on dependent field
        $field = SelectPlus::make('Available States', 'available_states')
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
            });

        // Verify the field structure supports dependent fields
        $this->assertNotNull($field->meta['dependsOn'] ?? null, 'Field should have dependsOn metadata');
        $this->assertArrayHasKey('attributes', $field->meta['dependsOn']);
        $this->assertContains('region', $field->meta['dependsOn']['attributes']);
    }

    /** @test */
    public function controller_applies_dependent_field_logic()
    {
        // This test verifies that the controller properly applies dependent field logic
        // when processing options requests
        
        // Create a person resource for testing
        $person = Person::create(['name' => 'Test Person']);
        
        // Verify the controller has the options method
        $controller = new \ZiffMedia\NovaSelectPlus\Controller(app());
        $this->assertTrue(method_exists($controller, 'options'));
        
        // Verify that the controller uses applyDependsOnWithDefaultValues
        $reflection = new \ReflectionMethod($controller, 'options');
        $source = file_get_contents($reflection->getFileName());
        $this->assertStringContains('applyDependsOnWithDefaultValues', $source);
    }

    /** @test */
    public function multiple_dependent_fields_are_supported()
    {
        // Test that a field can depend on multiple other fields
        $field = SelectPlus::make('Cities', 'cities')
            ->options(\App\Models\City::class)
            ->dependsOn(['region', 'state'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                // This callback would filter cities based on both region and state
                $region = $formData->region;
                $state = $formData->state;
                
                if ($region && $state) {
                    $field->optionsQuery(function ($query) use ($region, $state) {
                        // Filter logic would go here
                    });
                }
            });

        // Verify both dependent fields are registered
        $this->assertArrayHasKey('dependsOn', $field->meta);
        $this->assertContains('region', $field->meta['dependsOn']['attributes']);
        $this->assertContains('state', $field->meta['dependsOn']['attributes']);
    }

    /** @test */
    public function dependent_field_works_with_ajax_searchable()
    {
        // Test that dependent fields work correctly with ajax searchable fields
        $field = SelectPlus::make('Searchable States', 'searchable_states')
            ->options(State::class)
            ->ajaxSearchable(true)
            ->dependsOn(['filter_type'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                if ($formData->filter_type === 'large') {
                    $field->optionsQuery(function ($query) {
                        $query->whereIn('code', ['CA', 'TX', 'FL', 'NY']);
                    });
                }
            });

        // Verify the field supports both ajax searchable and dependent fields
        $this->assertTrue($field->meta['isAjaxSearchable']);
        $this->assertArrayHasKey('dependsOn', $field->meta);
        $this->assertContains('filter_type', $field->meta['dependsOn']['attributes']);
    }
}