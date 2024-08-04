<?php

namespace LaravelDatabasePatching\Bases;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

class CommandBase extends Command
{
    /**
     * @var Application|null
     */
    protected $app;
    /**
     * @var Filesystem|mixed|\Symfony\Component\Console\Command\Command
     */
    protected $files;

    protected $table='sql_patches';
    /**
     * @var Resolver
     */
    protected $resolver;

    /**
     * Create a new command instance.
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(Resolver $resolver)
    {
        parent::__construct();

        $this->app = app();

        $this->resolver = $resolver;
        $this->files = $this->app->get(Filesystem::class);
    }

    /**
     * Get patch path.
     *
     * @return string
     */
    protected function getPatchPath(): string
    {
        return $this->laravel->databasePath() . DIRECTORY_SEPARATOR . 'patches';
    }

    /**
     * Get path base laravel project directory.
     *
     * @param $path
     * @return string
     */
    protected function getLaravelPathBase(): string
    {
        return $this->laravel->basePath(). DIRECTORY_SEPARATOR;
    }

    protected function repositoryExists()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        return $schema->hasTable($this->table);
    }

    protected function getTable()
    {
        return $this->getConnection()->table($this->table)->useWritePdo();
    }

    protected function getConnection()
    {
        return $this->resolver->connection();
    }
}
