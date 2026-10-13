<?php

namespace LucaCermenati\NovaGlobalFilters;

use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use LucaCermenati\NovaGlobalFilters\Registry\FilterRegistry;
use LucaCermenati\NovaGlobalFilters\Storage\FilterStore;

class NovaGlobalFiltersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nova-global-filters.php', 'nova-global-filters');

        $this->app->singleton(FilterRegistry::class);

        $this->app->singleton(FilterStore::class, fn ($app) => new FilterStore(
            $app->make(Session::class),
            $app['config']->get('nova-global-filters.session_prefix', 'nova-global-filters'),
            $app['config']->get('nova-global-filters.default_scope', 'default'),
        ));

        $this->app->singleton(GlobalFilterManager::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/nova-global-filters.php' => config_path('nova-global-filters.php'),
        ], 'nova-global-filters-config');

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/nova-global-filters'),
        ], 'nova-global-filters-lang');

        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang');

        $this->app->booted(fn () => $this->routes());

        Nova::serving(function (ServingNova $event) {
            Nova::mix('nova-global-filters', __DIR__.'/../dist/mix-manifest.json');
            Nova::translations(__DIR__.'/../resources/lang/'.$event->app->getLocale().'.json');
        });
    }

    protected function routes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware($this->app['config']->get('nova-global-filters.middleware') ?? $this->app['config']->get('nova.api_middleware'))
            ->prefix('nova-vendor/nova-global-filters')
            ->group(__DIR__.'/../routes/api.php');
    }
}
