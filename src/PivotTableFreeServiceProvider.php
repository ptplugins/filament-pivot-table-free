<?php

namespace PtPlugins\FilamentPivotTableFree;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PivotTableFreeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'pivot-free');

        Blade::componentNamespace(
            'PtPlugins\\FilamentPivotTableFree\\View\\Components',
            'pivot-free',
        );
    }
}
