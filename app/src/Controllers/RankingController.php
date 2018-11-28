<?php

namespace ReviewHeroes\Controllers;

use ReviewHeroes\Domain\Ranking\GetRankingCommenters;
use ReviewHeroes\Domain\Ranking\GetRankingParticipation;
use ReviewHeroes\Domain\Ranking\GetRankingApprovers;
use ReviewHeroes\Domain\Ranking\GetRankingPickies;
use ReviewHeroes\Domain\Ranking\GetRankingPendingReviews;
use ReviewHeroes\Domain\Ranking\GetRankingBuildFailures;

use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RankingController
{
    const RANKING_LIMIT = 5;

    private $app;

    public function __construct(
        Application $app
    )
    {
        $this->app = $app;
    }

    public function show(Request $request)
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');

        if (!$startDate || !$endDate) {
            $startDate = (new \DateTime("-1 month"))->format('Y-m-d');
            $endDate = (new \DateTime())->format('Y-m-d');
        }

        $participations = $this->app['domain.ranking.get_ranking_participation']->run($startDate, $endDate, self::RANKING_LIMIT);
        $approves = $this->app['domain.ranking.get_ranking_approvers']->run($startDate, $endDate, self::RANKING_LIMIT);
        $pickies = $this->app['domain.ranking.get_ranking_pickies']->run($startDate, $endDate, self::RANKING_LIMIT);
        $nonFinished = $this->app['domain.ranking.get_ranking_pending_reviews']->run($startDate, $endDate, self::RANKING_LIMIT);
        $breakers = $this->app['domain.ranking.get_ranking_build_failures']->run($startDate, $endDate, self::RANKING_LIMIT);
        $commenters = $this->app['domain.ranking.get_ranking_commenters']->run($startDate, $endDate, self::RANKING_LIMIT);

        return $this->app['twig']->render('ranking/ranking.html.twig',
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
    }

    public function showFullList($id, Request $request)
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');

        if (!$startDate || !$endDate) {
            $startDate = (new \DateTime("-1 month"))->format('Y-m-d');
            $endDate = (new \DateTime())->format('Y-m-d');
        }

        switch ($id) {

            case 'participations':
                $ranking = $this->app['domain.ranking.get_ranking_participation']->run($startDate, $endDate);
                $ranking_title = 'Top Participations';
                break;
            case 'unfinished':
                $ranking = $this->app['domain.ranking.get_ranking_pending_reviews']->run($startDate, $endDate);
                $ranking_title = 'Top Unfinished Reviews';
                break;
            case 'approvals':
                $ranking = $this->app['domain.ranking.get_ranking_approvers']->run($startDate, $endDate);
                $ranking_title = 'Top Approvals';
                break;
            case 'pickies':
                $ranking = $this->app['domain.ranking.get_ranking_pickies']->run($startDate, $endDate);
                $ranking_title = 'Top Request For Changes';
                break;
            case 'breakers':
                $ranking = $this->app['domain.ranking.get_ranking_build_failures']->run($startDate, $endDate);
                $ranking_title = 'Top Build Breaks';
                break;
            case 'comments':
                $ranking = $this->app['domain.ranking.get_ranking_commenters']->run($startDate, $endDate);
                $ranking_title = 'Top Comments';
                break;

            default: return new NotFoundHttpException();
        }

        return $this->app['twig']->render('ranking/ranking_full_list.html.twig',
            [
                'ranking' => $ranking,
                'ranking_title' => $ranking_title,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        );
    }

    public function getStats($id, Request $request)
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');

        if (!$startDate || !$endDate) {
            $startDate = (new \DateTime("-1 month"))->format('Y-m-d');
            $endDate = (new \DateTime())->format('Y-m-d');
        }

        switch ($id) {

            case 'participations':
                return new JsonResponse(
                    $this->app['domain.user.get_user_profile_stats']->run(null, $startDate, $endDate)
                );
            case 'breakers':
                return new JsonResponse(
                    $this->app['domain.user.get_user_build_stats']->run(null, $startDate, $endDate)
                );

            default:
                return new NotFoundHttpException();
        }
    }
}