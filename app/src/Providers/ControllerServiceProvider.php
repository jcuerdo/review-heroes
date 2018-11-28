<?php

namespace ReviewHeroes\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ReviewHeroes\Controllers\GithubEventPayload;
use ReviewHeroes\Controllers\NotificationController;
use ReviewHeroes\Controllers\GraphController;
use ReviewHeroes\Controllers\ProfileController;
use ReviewHeroes\Controllers\RankingController;
use ReviewHeroes\Controllers\UserListController;
use ReviewHeroes\Controllers\SubscribeController;

class ControllerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['profile.controller'] = function($app) {
            return new ProfileController($app);
        };

        $app['user_list.controller'] = function($app) {
            return new UserListController($app);
        };

        $app['subscribe.controller'] = function($app) {
            return new SubscribeController($app);
        };

        $app['notification.controller'] = function($app) {
            return new NotificationController($app);
        };

        $app['ranking.controller'] = function($app) {
            return new RankingController($app);
        };

        $app['graph.controller'] = function($app) {
            return new GraphController(
                $app
            );
        };

        $app['githubEventPayload.controller'] = function($app) {
            return new GithubEventPayload(
                $app['domain.github.event_handler']
            );
        };
    }
}