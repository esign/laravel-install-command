<?php

namespace Esign\InstallCommand;

use Illuminate\Support\ServiceProvider;

class InstallCommandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([$this->configPath() => config_path('install-command.php')], 'config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'install-command');

        $this->app->singleton('install-command', function () {
            return new InstallCommand;
        });
    }

    protected function configPath(): string
    {
        return __DIR__ . '/../config/install-command.php';
    }
}
