<?php

namespace App\Console\Commands;

use App\Lib\Indexer;
use App\Lib\Path;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class IndexCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new path records to the database';

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
        $basePath = config('app.manga_path');
        $path = new Path($basePath);

        $count = 0;
        Indexer::index($path, null, $count);

        $this->info(sprintf('Added %s new paths', $count));
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [

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

        ];
    }
}
