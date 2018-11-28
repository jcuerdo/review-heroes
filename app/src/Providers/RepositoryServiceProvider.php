<?php

namespace ReviewHeroes\Providers;

use Milo\Github\Api;
use Milo\Github\OAuth\Token;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ReviewHeroes\Repositories\BuildRepository;
use ReviewHeroes\Repositories\GithubRepository;
use ReviewHeroes\Repositories\NotificationRepository;
use ReviewHeroes\Repositories\ParticipationsRepository;
use ReviewHeroes\Repositories\UserRepository;

class RepositoryServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['db'] = function() use ($app) {

            $host = $app['config']['mysql']['host'];
            $dbname = $app['config']['mysql']['dbname'];
            $username = $app['config']['mysql']['username'];
            $password = $app['config']['mysql']['password'];

            $pdo = new \PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $pdo;
        };

        $app['user.repository'] = function($app) {
            return new UserRepository(
                $app['db'],
                $app['redis']
            );
        };

        $app['participations.repository'] = function($app) {
            return new ParticipationsRepository(
                $app['db'],
                $app['redis']
            );
        };

        $app['build.repository'] = function($app) {
            return new BuildRepository(
                $app['db'],
                $app['redis']
            );
        };

        $app['notification.repository'] = function($app) {
            return new NotificationRepository(
                $app['db'],
                $app['redis']
            );
        };

        $app['github'] = function() use ($app) {
            $api = new Api();
            $api->setToken(new Token($app['config']['github']['apitoken']));
            return $api;
        };

        $app['github.repository'] = function($app) {
            return new GithubRepository($app['github']);
        };
    }
}