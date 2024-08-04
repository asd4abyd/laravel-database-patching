<?php

namespace LaravelDatabasePatching\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use LaravelDatabasePatching\Bases\CommandBase;
use Str;

class MakePatchCommand extends CommandBase
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sql-patch:make {name : The name of the SQL patch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new SQL patch file';

    /**
     * Execute the console command.
     *
     * @return int
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $name = Str::snake(trim($this->input->getArgument('name')));
        $name = Str::studly($name);

        $path = $this->getPatchPath();
        $path = $this->getPath($name, $path);

        $this->writeMigration($name, $path);

        $this->comment('patch class has created');


        $path = str_replace($this->getLaravelPathBase(), '', $path);

        $this->info("class: {$path}");

        return 0;
    }

    /**
     * @param $name
     * @param $path
     * @return void
     * @throws FileNotFoundException
     */
    private function writeMigration($name, $path)
    {
        $dir = dirname($path);
        $this->files->ensureDirectoryExists($dir);

        $stub = $this->getStub();
        $stub = str_replace('{{ $class }}', Str::camel($name), $stub);

        $this->files->put($path, $stub);
    }

    /**
     * Get the migration stub file.
     *
     * @return string
     * @throws FileNotFoundException
     */
    protected function getStub()
    {
        return $this->files->get(
            __DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'stubs'
            . DIRECTORY_SEPARATOR . 'make.stub'
        );
    }

    /**
     * Get the full path to the migration.
     *
     * @param string $name
     * @param string $path
     * @return string
     */
    protected function getPath($name, $path)
    {
        return $path . '/' . $this->getDatePrefix() . '_' . $name . '.php';
    }

    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }
}
