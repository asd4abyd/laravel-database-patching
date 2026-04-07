<?php

namespace Dweik\LaravelDatabasePatching;

use Illuminate\Support\ServiceProvider;
use Dweik\LaravelDatabasePatching\Commands\InstallCommand;
use Dweik\LaravelDatabasePatching\Commands\MakePatchCommand;
use Dweik\LaravelDatabasePatching\Commands\RunCommand;

class PatchSQLServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->commands([
            MakePatchCommand::class,
            InstallCommand::class,
            RunCommand::class,
        ]);
    }
}
