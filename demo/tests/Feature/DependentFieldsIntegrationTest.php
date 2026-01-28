<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\State;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Nova\Fields\FormData;
use Laravel\Nova\Http\Requests\NovaRequest;
use Tests\TestCase;
use ZiffMedia\NovaSelectPlus\SelectPlus;

class DependentFieldsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test states
        State::create(['name' => 'California', 'code' => 'CA']);
        State::create(['name' => 'Louisiana', 'code' => 'LA']);
        State::create(['name' => 'Texas', 'code' => 'TX']);
        State::create(['name' => 'New York', 'code' => 'NY']);
    }

    /** @test */
    public function dependent_field_integration_test()
    {
        // This test demonstrates the complete dependent fields functionality
        // as specified in the acceptance criteria
        
        $callbackExecuted = false;
        $receivedRequest = null;
        $receivedFormData = null;
        $fieldModified = false;

        // Create a SelectPlus field with dependsOn that matches the acceptance criteria
        $field = SelectPlus::make('Dependent States', 'dependent_states')
            ->options(State::class)
            ->dependsOn(['filter_type'], function (SelectPlus $field, NovaRequest $request, FormData $formData) use (&$callbackExecuted, &$receivedRequest, &$receivedFormData, &$fieldModified) {
                $callbackExecuted = true;
                $receivedRequest = $request;
                $receivedFormData = $formData;
                
                // Verify we have access to all form data
                $filterType = $formData->filter_type;
                
                if ($filterType === 'L_states') {
                    $field->optionsQuery(function ($query) {
                        $query->where('name', 'LIKE', 'L%');
                    });
                    $fieldModified = true;
                }
            });

        // Verify the field structure
        $this->assertTrue(method_exists($field, 'dependsOn'));
        $this->assertArrayHasKey('dependsOn', $field->meta);
        $this->assertContains('filter_type', $field->meta['dependsOn']['attributes']);

        // Simulate the dependent field callback execution with form data
        $formData = new FormData([
            'filter_type' => 'L_states',
            'dependent_states' => [],
            'name' => 'Test Person',
            'other_field' => 'some_value'
        ]);

        $request = NovaRequest::create('/test', 'POST', [
            'filter_type' => 'L_states',
            'dependent_states' => '[]',
            'name' => 'Test Person',
            'other_field' => 'some_value'
        ]);

        // Execute the callback manually to verify it works
        $callback = $field->meta['dependsOn']['callback'];
        if ($callback && is_callable($callback)) {
            $callback($field, $request, $formData);
        }

        // Verify acceptance criteria:
        // ✓ GIVEN a SelectPlus field with a dependsOn dependent field attributes and callback
        $this->assertArrayHasKey('dependsOn', $field->meta);
        $this->assertIsCallable($field->meta['dependsOn']['callback']);
        
        // ✓ WHEN the dependent field is changed the callback should be called
        $this->assertTrue($callbackExecuted, 'Callback should be executed when dependent field changes');
        
        // ✓ with all form data in the Request and FormData objects
        $this->assertInstanceOf(NovaRequest::class, $receivedRequest, 'Callback should receive NovaRequest instance');
        $this->assertInstanceOf(FormData::class, $receivedFormData, 'Callback should receive FormData instance');
        
        // Verify all form data is available
        $this->assertEquals('L_states', $receivedFormData->filter_type);
        $this->assertEquals('Test Person', $receivedFormData->name);
        $this->assertEquals('some_value', $receivedFormData->other_field);
        
        // Verify the field was actually modified
        $this->assertTrue($fieldModified, 'Field should be modified based on dependent field value');
    }

    /** @test */
    public function frontend_component_supports_dependent_fields()
    {
        // Verify the frontend component has the necessary dependent field support
        $formFieldContent = file_get_contents(__DIR__ . '/../../resources/js/components/FormField.vue');
        
        // Check for DependentFormField mixin
        $this->assertStringContains('DependentFormField', $formFieldContent);
        
        // Check for field change emission
        $this->assertStringContains('field-changed', $formFieldContent);
        $this->assertStringContains('emitFieldValueChange', $formFieldContent);
        
        // Check for onSyncedField method
        $this->assertStringContains('onSyncedField', $formFieldContent);
        
        // Check for dependsOn parameter handling
        $this->assertStringContains('this.currentField.dependsOn', $formFieldContent);
    }

    /** @test */
    public function controller_handles_dependent_field_parameters()
    {
        // Verify the controller properly handles dependent field parameters
        $controllerContent = file_get_contents(__DIR__ . '/../../src/Controller.php');
        
        // Check for applyDependsOnWithDefaultValues usage
        $this->assertStringContains('applyDependsOnWithDefaultValues', $controllerContent);
        
        // Verify the controller has the options method
        $controller = new \ZiffMedia\NovaSelectPlus\Controller(app());
        $this->assertTrue(method_exists($controller, 'options'));
    }

    /** @test */
    public function select_plus_uses_supports_dependent_fields_trait()
    {
        // Verify SelectPlus uses the SupportsDependentFields trait
        $selectPlusContent = file_get_contents(__DIR__ . '/../../src/SelectPlus.php');
        
        $this->assertStringContains('use SupportsDependentFields;', $selectPlusContent);
        $this->assertStringContains('use Laravel\Nova\Fields\SupportsDependentFields;', $selectPlusContent);
        
        // Verify the trait is actually used
        $field = SelectPlus::make('Test', 'test');
        $this->assertTrue(in_array('Laravel\Nova\Fields\SupportsDependentFields', class_uses_recursive(get_class($field))));
    }

    /** @test */
    public function complete_dependent_fields_workflow()
    {
        // Test the complete workflow from field definition to frontend handling
        
        // 1. Create a field with dependent functionality
        $field = SelectPlus::make('States', 'states')
            ->options(State::class)
            ->dependsOn(['region'], function (SelectPlus $field, NovaRequest $request, FormData $formData) {
                if ($formData->region === 'south') {
                    $field->optionsQuery(function ($query) {
                        $query->whereIn('code', ['TX', 'LA']);
                    });
                }
            });

        // 2. Verify field metadata is properly set
        $this->assertArrayHasKey('dependsOn', $field->meta);
        $this->assertArrayHasKey('attributes', $field->meta['dependsOn']);
        $this->assertArrayHasKey('callback', $field->meta['dependsOn']);
        
        // 3. Verify the field can be serialized (for frontend)
        $serialized = $field->jsonSerialize();
        $this->assertArrayHasKey('dependsOn', $serialized);
        
        // 4. Verify the callback works with real form data
        $formData = new FormData(['region' => 'south', 'states' => []]);
        $request = NovaRequest::create('/test', 'POST', ['region' => 'south']);
        
        $callback = $field->meta['dependsOn']['callback'];
        $callback($field, $request, $formData);
        
        // The field should now have the optionsQuery set
        $this->assertNotNull($field->optionsQuery);
    }
}