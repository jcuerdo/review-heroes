<?php

namespace ReviewHeroes\Routes;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class RoutesControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function (Application $app, Request $request) {
            return $app['ranking.controller']->show($request);
        })->bind('ranking');

        $controllers->get('/ranking/{id}', function (Application $app, Request $request, $id) {
            return $app['ranking.controller']->showFullList($id, $request);
        })->bind('ranking_full_list');

        $controllers->get('/ranking/{id}/stats', function (Application $app, $id, Request $request) {
            return $app['ranking.controller']->getStats($id, $request);
        })->bind('ranking_full_list_stats');

        $controllers->get('/profile/{id}/participation-stats', function (Application $app, $id) {
            return $app['profile.controller']->getParticipationStats($id);
        })->bind('participation-stats');

        $controllers->get('/profile/{id}/build-stats', function (Application $app, $id) {
            return $app['profile.controller']->getBuildStats($id);
        })->bind('build-stats');

        $controllers->get('/profile/{id}', function (Application $app, $id) {
            return $app['profile.controller']->getProfile($id);
        })->bind('profile');

        $controllers->get('/users', function (Application $app) {
            return $app['user_list.controller']->getAll();
        })->bind('users');

        $controllers->get('/graph/{id}', function (Application $app, Request $request, $id) {
            return $app['graph.controller']->show($id, $request);
        })->bind('participation-graph');

        $controllers->post('/payload', function(Application $app,Request $request) {
            return $app['githubEventPayload.controller']->handleEvent($request);
        })->bind('payload');

        $controllers->get('/subscribe', function (Application $app) {
            return $app['subscribe.controller']->getAll();
        })->bind('subscribe');

        $controllers->post('/subscribe', function (Application $app, Request $request) {
            return $app['subscribe.controller']->submit($request);
        })->bind('subscribe_post');

        $controllers->get('/api/graph/{id}', function (Application $app, Request $request, $id) {
            return $app['graph.controller']->graph($id, $request);
        })->bind('graph-api');

        return $controllers;
  }
}
