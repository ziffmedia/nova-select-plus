<?php

namespace Tests\Feature;

use Laravel\Nova\Http\Middleware\Authenticate;
use Laravel\Nova\Http\Middleware\Authorize;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use Tests\Fixtures\GadgetResource;
use Tests\Fixtures\RejectRequest;
use Tests\Fixtures\WidgetResource;
use Tests\TestCase;

class OptionsAuthorizationTest extends TestCase
{
    public const URI = '/nova-vendor/select-plus/widgets/';

    public function test_guests_are_rejected(): void
    {
        foreach (['gadgets', 'other_gadgets', 'colors'] as $attribute) {
            $this->getJson(self::URI.$attribute)->assertUnauthorized();
        }
    }

    public function test_users_who_fail_the_view_nova_gate_are_forbidden(): void
    {
        $this->actingAs($this->novaUser(viewNova: false))->getJson(self::URI.'colors')->assertForbidden();
    }

    public function test_authorized_users_get_options_from_a_relationship(): void
    {
        $this->actingAs($this->novaUser())->getJson(self::URI.'gadgets')->assertOk()->assertJsonFragment(['label' => 'Sprocket']);
    }

    public function test_authorized_users_get_options_from_a_model_class(): void
    {
        $this->actingAs($this->novaUser())->getJson(self::URI.'other_gadgets')->assertOk()->assertJsonFragment(['label' => 'Sprocket']);
    }

    public function test_authorized_users_get_options_from_an_array(): void
    {
        $this->actingAs($this->novaUser())->getJson(self::URI.'colors')->assertOk()->assertJsonFragment(['label' => 'Red']);
    }

    public function test_users_who_cannot_view_the_resource_are_forbidden(): void
    {
        WidgetResource::$denyViewAny = true;

        $this->actingAs($this->novaUser())->getJson(self::URI.'colors')->assertForbidden();
    }

    public function test_users_who_cannot_see_the_field_are_forbidden(): void
    {
        $this->actingAs($this->novaUser())->getJson(self::URI.'hidden')->assertForbidden();
    }

    public function test_users_who_cannot_see_a_field_requested_by_field_id_are_forbidden(): void
    {
        $this->actingAs($this->novaUser())->getJson(self::URI.'identified?fieldId=identified-field')->assertForbidden();
    }

    public function test_users_who_cannot_view_the_related_resource_are_forbidden(): void
    {
        GadgetResource::$denyViewAny = true;

        $this->actingAs($this->novaUser())->getJson(self::URI.'gadgets')->assertForbidden();
        $this->actingAs($this->novaUser())->getJson(self::URI.'other_gadgets')->assertForbidden();
        $this->actingAs($this->novaUser())->getJson(self::URI.'colors')->assertOk();
    }

    #[DefineEnvironment('useRejectingApiMiddleware')]
    public function test_the_route_uses_the_apps_nova_api_middleware(): void
    {
        $this->actingAs($this->novaUser())->getJson(self::URI.'colors')->assertStatus(418);
    }

    protected function useRejectingApiMiddleware($app): void
    {
        $app['config']->set('nova.api_middleware', ['nova', Authenticate::class, Authorize::class, RejectRequest::class]);
    }
}
