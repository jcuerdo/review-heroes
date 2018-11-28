<?php

namespace ReviewHeroes\Controllers;

use Silex\Application;

class UserListController
{
    private $app;

    public function __construct(
        Application $app

    )
    {
        $this->app = $app;
    }

    public function getAll()
    {
        $users = $this->app['user.repository']->getAll();

        list($users_col_1, $users_col_2) = array_chunk($users, ceil(count($users) / 2));

        return $this->app['twig']->render(
            'heroes/users.html.twig',
            [
                'users_col_1' => $users_col_1,
                'users_col_2' => $users_col_2
            ]
        );
    }
}