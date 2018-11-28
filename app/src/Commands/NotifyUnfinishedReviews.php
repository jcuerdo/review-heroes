<?php

namespace ReviewHeroes\Commands;

use ReviewHeroes\Controllers\NotificationController;
use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class NotifyUnfinishedReviews extends \Knp\Command\Command
{
    private $app;

    public function __construct($name = null, $app)
    {
        parent::__construct($name);
        $this->app = $app;
    }

    protected function configure() {
        $this
            ->setName("notify-unfinished-reviews")
            ->setDescription("Notify Unfinished Reviews");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->app->run();
            /** @var NotificationController $notificationController */
            $notificationController = $this->app['notification.controller'];
            $notificationController->notifyUnfinishedReviews();
            $output->writeln('<bg=green;fg=black;option=blink>Notification Success!</>');
        } catch (\Exception $e) {
            $output->writeln("<bg=red;fg=black;option=blink>{$e->getMessage()}</>");
        }
    }
}