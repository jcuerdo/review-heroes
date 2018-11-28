<?php

namespace ReviewHeroes\Controllers;

use ReviewHeroes\Notifiers\SlackNotifier;
use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

class NotificationController
{
    const RANKING_LIMIT = 5;

    private $app;

    public function __construct(
        Application $app

    )
    {
        $this->app = $app;
    }

    public function broadcastStats()
    {
        $startDate = null;
        $endDate = null;

        $participations = $this->app['domain.ranking.get_ranking_participation']->run($startDate, $endDate, self::RANKING_LIMIT);
        $approves = $this->app['domain.ranking.get_ranking_approvers']->run($startDate, $endDate, self::RANKING_LIMIT);
        $pickies = $this->app['domain.ranking.get_ranking_pickies']->run($startDate, $endDate, self::RANKING_LIMIT);
        $nonFinished = $this->app['domain.ranking.get_ranking_pending_reviews']->run($startDate, $endDate, self::RANKING_LIMIT);
        $breakers = $this->app['domain.ranking.get_ranking_build_failures']->run($startDate, $endDate, self::RANKING_LIMIT);
        $commenters = $this->app['domain.ranking.get_ranking_commenters']->run($startDate, $endDate, self::RANKING_LIMIT);

        /** @var \Twig_Environment $twig */
        $twig = $this->app['twig'];

        $content = $twig->render('ranking/email/ranking_list.html.twig',
            [
                'participations' => $participations,
                'approvers' => $approves,
                'pickies' => $pickies,
                'nonFinished' => $nonFinished,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'breakers' => $breakers,
                'commenters' => $commenters
            ]
        );

        $this->app['domain.notification.broadcast_stats_by_email']->run($content);
        return new Response();
    }

    public function monthlyStats()
    {
        $startDate = (new \DateTime("-1 month"))->format('Y-m-d');
        $endDate = (new \DateTime())->format('Y-m-d');

        $participations = $this->app['domain.ranking.get_ranking_participation']->run($startDate, $endDate, self::RANKING_LIMIT);
        $approves = $this->app['domain.ranking.get_ranking_approvers']->run($startDate, $endDate, self::RANKING_LIMIT);
        $pickies = $this->app['domain.ranking.get_ranking_pickies']->run($startDate, $endDate, self::RANKING_LIMIT);
        $nonFinished = $this->app['domain.ranking.get_ranking_pending_reviews']->run($startDate, $endDate, self::RANKING_LIMIT);
        $breakers = $this->app['domain.ranking.get_ranking_build_failures']->run($startDate, $endDate, self::RANKING_LIMIT);
        $commenters = $this->app['domain.ranking.get_ranking_commenters']->run($startDate, $endDate, self::RANKING_LIMIT);

        /** @var \Twig_Environment $twig */
        $twig = $this->app['twig'];

        $content = $twig->render('ranking/email/ranking_list.html.twig',
            [
                'participations' => $participations,
                'approvers' => $approves,
                'pickies' => $pickies,
                'nonFinished' => $nonFinished,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'breakers' => $breakers,
                'commenters' => $commenters
            ]
        );

        $this->app['domain.notification.broadcast_stats_by_email']->run($content);
        return new Response();
    }

    public function notifyUnfinishedReviews()
    {
        $slack_notifier = $this->app['slack_notifier'];

        $yesterday = date("Y-m-d H:i:s", strtotime('-1 day', strtotime(date("Y-m-d H:i:s"))));
        $today = date("Y-m-d H:i:s");
        $results = $this->app['participations.repository']->pendingReviews($yesterday, $today);

        foreach ($results as $result) {

            $content = "
                    :rage: Ey *{$result['username']}* ! You have an unfinished review related to this merged PR {$result['pullRequestUrl']} 
                ";

            $slack_notifier->send(
                $result['username'],
                [],
                $content,
                'Unfinished Reviews',
                'You have an Unfinished Reviews',
                ':rage:'
            );
        }

        return new Response();
    }
}