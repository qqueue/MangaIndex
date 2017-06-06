<?php

namespace App\Console\Commands;

use App\Lib\DisplaySize;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UpdateSizeCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:update-size';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update size stat';

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
        $size = DB::table('path_records')->sum('size');
        Cache::put('statTotalSize', $size, 60);

        $formatted = DisplaySize::format($size, 2);
        $this->info($formatted);
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
            //array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        ];
    }
}
