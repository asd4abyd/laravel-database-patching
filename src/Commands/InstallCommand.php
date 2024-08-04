<?php

namespace LaravelDatabasePatching\Commands;


use LaravelDatabasePatching\Bases\CommandBase;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends CommandBase
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'sql-patch:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the SQL Patches repository';


    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $schema = $this->getConnection()->getSchemaBuilder();

        $schema->create($this->table, function ($table) {
            $table->increments('id');
            $table->string('patch_file');
            $table->integer('batch');
        });

        $this->info('SQLPatches table created successfully.');
    }

}
