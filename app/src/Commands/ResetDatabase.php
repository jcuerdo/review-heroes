<?php

namespace ReviewHeroes\Commands;

use \Symfony\Component\Console\Input\InputInterface;
use \Symfony\Component\Console\Output\OutputInterface;

class ResetDatabase extends \Knp\Command\Command {

    protected function configure() {
        $this
          ->setName("reset-database")
          ->setDescription("Reset Database");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        try {

            $output->writeln('<comment>Reset Database!</comment>');

            $pdo = new \PDO("mysql:host=reviewheroes_db_1", "root", "123456");
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $sql = file_get_contents(__DIR__.'/../../db/schema.sql');
            $query = $pdo->prepare($sql, [\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true]);
            $result = $query->execute();

            if ($result === true) {
                $output->writeln('<bg=green;fg=black;option=blink>Reset Database Success!</>');
            } else {
                $output->writeln('<bg=red;fg=black;option=blink>Reset Database Error!</>');
            }

        } catch (\PDOException $e ) {
            $output->writeln($e->getMessage());
        }

    }

}
