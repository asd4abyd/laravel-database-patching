<?php

namespace LaravelDatabasePatching;

use Illuminate\Support\ServiceProvider;
use LaravelDatabasePatching\Commands\InstallCommand;
use LaravelDatabasePatching\Commands\MakePatchCommand;
use LaravelDatabasePatching\Commands\RunCommand;
use LaravelDatabasePatching\Commands\BaseCommand;

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
