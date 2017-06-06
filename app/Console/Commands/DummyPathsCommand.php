<?php

namespace App\Console\Commands;

use App\PathRecord;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DummyPathsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:dummy-paths';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create paths on disk that mirror path records in the database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {

        $target = $this->option('path');

        PathRecord::orderBy('directory', 'desc')->chunk(200, function ($pathRecords) use ($target) {

            foreach ($pathRecords as $pathRecord) {
                if ($pathRecord->path === '/') {
                    continue;
                }

                $fullPath = $target.'/'.$pathRecord->path;

                if (file_exists($fullPath)) {
                    continue;
                }

                if ($pathRecord->directory) {
                    try {
                        mkdir($fullPath, 0777, true);
            //printf("%s\n", $fullPath);
                    } catch (Exception $e) {
                        var_dump($fullPath);
                    }
                } else {
                    try {
                        touch($fullPath);
                    } catch (Exception $e) {
                        var_dump($fullPath);
                    }
                }
            }
        });
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            //array('example', InputArgument::REQUIRED, 'An example argument.'),
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['path', null, InputOption::VALUE_REQUIRED, 'Target path', null],
        ];
    }
}
