<?php

namespace App\Console\Commands;

use App\Series;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UpdateSeriesCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'command:update-series';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update series meta';

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
        while (true) {
            $result = Series::whereNeedsUpdate(true)->take(100)->get();

            if (count($result) === 0) {
                break;
            }

            foreach ($result as $series) {
                printf("%s (#%d)\n", $series->name, $series->id);
                $series->updateMuData();
            }
        }
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
