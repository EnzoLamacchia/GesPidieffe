<?php

namespace Elamacchia\Gespidieffe;

use Elamacchia\Gespidieffe\Console\PulisciTmpCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class GespidieffeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'gespidieffe');

        // Registra i componenti blade anonimi del package (es. <x-gespidieffe::layouts.app>)
        Blade::anonymousComponentNamespace(
            __DIR__ . '/resources/views/components',
            'gespidieffe'
        );
        // Layout anonimi (es. <x-gespidieffe::layouts.app>)
        Blade::anonymousComponentPath(
            __DIR__ . '/resources/views',
            'gespidieffe'
        );

        if ($this->app->runningInConsole()) {
            $this->commands([PulisciTmpCommand::class]);
        }

        // Pulizia automatica ogni ora: rimuove file tmp più vecchi di 24 ore
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('gespidieffe:pulisci-tmp --ore=24')->hourly();
        });
    }

    public function register(): void
    {
        //
    }
}
