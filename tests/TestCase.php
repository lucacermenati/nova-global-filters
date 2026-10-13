<?php

namespace LucaCermenati\NovaGlobalFilters\Tests;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\NovaCoreServiceProvider;
use LucaCermenati\NovaGlobalFilters\Facades\NovaGlobalFilters;
use LucaCermenati\NovaGlobalFilters\Filters\DateRangeFilter;
use LucaCermenati\NovaGlobalFilters\GlobalFilterManager;
use LucaCermenati\NovaGlobalFilters\NovaGlobalFiltersServiceProvider;
use LucaCermenati\NovaGlobalFilters\Tests\Fixtures\CountryFilter;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        Schema::create('posts', function ($table) {
            $table->id();
            $table->string('status')->default('draft');
            $table->string('country')->nullable();
            $table->integer('amount')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Gate::define('viewNova', fn ($user = null) => true);
        Nova::auth(fn () => true);

        NovaGlobalFilters::registry()->flush();
        NovaGlobalFilters::register([DateRangeFilter::make(), CountryFilter::make()]);
    }

    protected function getPackageProviders($app): array
    {
        return [
            InertiaServiceProvider::class,
            NovaCoreServiceProvider::class,
            NovaGlobalFiltersServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('app.timezone', 'UTC');
    }

    protected function manager(): GlobalFilterManager
    {
        return $this->app->make(GlobalFilterManager::class);
    }

    /**
     * A dashboard metric request, as the Nova frontend sends it.
     */
    protected function metricRequest(array $parameters = [], string $dashboard = 'main', string $metric = 'metric'): NovaRequest
    {
        $request = NovaRequest::create("/nova-api/dashboards/cards/{$dashboard}/metrics/{$metric}", 'GET', $parameters);
        $request->headers->set('Accept', 'application/json');

        $this->app->instance('request', $request);

        return $request;
    }

    protected function user(): User
    {
        return User::query()->forceCreate([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
