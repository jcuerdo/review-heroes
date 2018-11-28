<?php

namespace ReviewHeroes\Commands;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class MonthlyStats extends \Knp\Command\Command
{
    private $app;

    public function __construct($name = null, $app)
    {
        parent::__construct($name);
        $this->app = $app;
    }

    protected function configure() {
        $this
            ->setName("monthly-stats")
            ->setDescription("Monthly Review Awards");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->app->run();
            $this->app['notification.controller']->monthlyStats();
            $output->writeln('<bg=green;fg=black;option=blink>Monthly stats Success!</>');
        } catch (\Exception $e) {
            $output->writeln("<bg=red;fg=black;option=blink>{$e->getMessage()}</>");
        }
    }
}