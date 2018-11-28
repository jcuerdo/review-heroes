<?php

namespace ReviewHeroes\Commands;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class BroadcastStats extends \Knp\Command\Command
{
    private $app;

    public function __construct($name = null, $app)
    {
        parent::__construct($name);
        $this->app = $app;
    }

    protected function configure() {
        $this
            ->setName("broadcast-stats")
            ->setDescription("Broadcast Stats");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->app->run();
            $this->app['notification.controller']->broadCastStats();
            $output->writeln('<bg=green;fg=black;option=blink>Broadcast Success!</>');
        } catch (\Exception $e) {
            $output->writeln("<bg=red;fg=black;option=blink>{$e->getMessage()}</>");
        }
    }
}