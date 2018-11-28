<?php

namespace ReviewHeroes\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfileController
{
    private $app;

    public function __construct(
        Application $app

    )
    {
        $this->app = $app;
    }

    public function getProfile($id)
    {
        $profile = $this->app['domain.user.get_user_profile']->run($id);

        return $this->app['twig']->render(
            'profile/profile.html.twig',
            [
                'pr_stats' => $profile['pr_stats'],
                'prs' => $profile['prs'],
                'user' => $profile['user'],
                'build_stats' => $profile['build_stats'],
            ]
        );
    }

    public function getBuildStats($id)
    {
        return new JsonResponse($this->app['domain.user.get_user_build_stats']->run($id));
    }

    public function getParticipationStats($id)
    {
        return new JsonResponse($this->app['domain.user.get_user_profile_stats']->run($id));
    }
}