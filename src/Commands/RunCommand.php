<?php

namespace LaravelDatabasePatching\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelDatabasePatching\Bases\CommandBase;

class RunCommand extends CommandBase
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sql-patch {--database= : The database connection to use}
                {--no-transaction : Force the operation to run when in production}
                {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the SQL patches transactional';

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws FileNotFoundException
     * @throws \Throwable
     */
    public function handle()
    {
        if (!$this->confirmToProceed()) {
            return 1;
        }

        $this->context();

        $this->prepareDatabase();

        $files = $this->getPatchFiles($this->getPatchPath());

        $this->requireFiles($patches = $this->pendingPatches(
            $files, $this->getRan()
        ));

        $this->runPending($patches, [
            'no-transaction' => $this->option('no-transaction'),
        ]);

        return 0;
    }

    /**
     * Get the patch files that have not yet run.
     *
     * @param array $files
     * @param array $ran
     *
     * @return array
     */
    protected function pendingPatches($files, $ran)
    {
        return Collection::make($files)
            ->reject(function ($file) use ($ran) {
                return in_array($this->getPatchName($file), $ran);
            })->values()->all();
    }

    /**
     * Get the name of the patch.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getPatchName($path)
    {
        return str_replace('.php', '', basename($path));
    }

    /**
     * Require in all the patches files in a given path.
     *
     * @param array $files
     * @return void
     *
     * @throws FileNotFoundException
     */
    public function requireFiles(array $files)
    {
        foreach ($files as $file) {
            $this->files->requireOnce($file);
        }
    }

    /**
     * Prepare the patch database for running.
     *
     * @return void
     */
    protected function prepareDatabase()
    {
        if (!$this->repositoryExists()) {
            $this->call('sql-patch:install');
        }
    }

    protected function getPatchFiles($paths)
    {
        return Collection::make($paths)->flatMap(function ($path) {
            return Str::endsWith($path, '.php') ? [$path] : $this->files->glob($path . '/*_*.php');
        })->filter()->values()->keyBy(function ($file) {
            return $this->getPatchName($file);
        })->sortBy(function ($file, $key) {
            return $key;
        })->all();
    }

    protected function getPatchClass(string $patchName): string
    {
        return Str::studly(implode('_', array_slice(explode('_', $patchName), 4)));
    }

    protected function getRan()
    {
        return $this->getTable()
            ->orderBy('batch', 'asc')
            ->orderBy('patch_file', 'asc')
            ->pluck('patch_file')->all();
    }

    /**
     * Run an array of patches.
     *
     * @param array $pathces
     * @param array $options
     *
     * @return void
     *
     * @throws FileNotFoundException
     * @throws \Throwable
     */
    protected function runPending(array $pathces, array $options = [])
    {
        if (count($pathces) === 0) {
            $this->info('<info>Nothing to run.</info>');

            return;
        }

        $batch = $this->getNextBatchNumber();

        $noTransaction = $options['no-transaction'] ?? false;

        foreach ($pathces as $file) {
            $this->runHandle($file, $batch, $noTransaction);
        }
    }

    public function getNextBatchNumber()
    {
        return $this->getTable()->max('batch') + 1;
    }

    /**
     * Run patch instance.
     *
     * @param string $file
     * @param int $batch
     * @param bool $noTransaction
     *
     * @return void
     *
     * @throws FileNotFoundException
     * @throws \Throwable
     *
     */
    protected function runHandle($file, $batch, $noTransaction)
    {
        $thePatch = $this->resolvePath($file);

        $name = $this->getPatchName($file);

        $this->output->writeln("<comment>Run patch:</comment> {$name}");

        $startTime = microtime(true);

        if(!$noTransaction){
            $thePatch->handle();
        }
        else {
            \DB::transaction(function () use($thePatch){
                $thePatch->handle();
            });
        }


        $runTime = number_format((microtime(true) - $startTime) * 1000, 2);

        $this->log($name, $batch);

        $this->output->writeln("<info>Patch:</info>  {$name} ({$runTime}ms)");
    }


    /**
     * Resolve a patch instance from a patch path.
     *
     * @param string $path
     *
     * @return object
     *
     * @throws FileNotFoundException
     */
    protected function resolvePath(string $path)
    {
        $class = $this->getPatchClass($this->getPatchName($path));

        if (class_exists($class) && realpath($path) == (new \ReflectionClass($class))->getFileName()) {
            return new $class;
        }

        $thePatch = $this->files->getRequire($path);

        return is_object($thePatch) ? $thePatch : new $class;
    }

    public function log($file, $batch)
    {
        $record = ['patch_file' => $file, 'batch' => $batch];

        $this->getTable()->insert($record);
    }
}
