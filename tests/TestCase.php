<?php

namespace Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaCoreServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Tests\Fixtures\Gadget;
use Tests\Fixtures\GadgetResource;
use Tests\Fixtures\WidgetResource;
use ZiffMedia\NovaSelectPlus\FieldServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Nova keeps these in static properties that outlive a test.
        Nova::$resources = [];
        Nova::$authUsing = null;
        WidgetResource::$denyViewAny = false;
        GadgetResource::$denyViewAny = false;

        Nova::resources([WidgetResource::class, GadgetResource::class]);
        Nova::serving(fn () => Nova::auth(fn ($request) => (bool) $request->user()?->view_nova));

        Schema::create('widgets', fn (Blueprint $table) => $table->id());
        Schema::create('gadgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('widget_id')->nullable();
            $table->string('name');
            $table->timestamps();
        });

        Gadget::create(['name' => 'Sprocket']);
    }

    protected function getPackageProviders($app): array
    {
        // Testbench turns package discovery off, so list the providers the tests need.
        return [
            InertiaServiceProvider::class,
            NovaCoreServiceProvider::class,
            FieldServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        $app['config']->set('database.default', 'testing');
    }

    protected function novaUser(bool $viewNova = true): User
    {
        return (new User)->forceFill(['id' => 1, 'view_nova' => $viewNova]);
    }
}
