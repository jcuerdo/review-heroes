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

class GraphController
{
    const RANKING_LIMIT = 5;

    private $app;

    public function __construct(
        Application $app
    )
    {
        $this->app = $app;
    }

    public function graph($id, Request $request)
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $userList = $request->get('userlist');

        if($userList){

            $userList = explode(',', $userList);
            foreach ($userList as $key => $user){
                $userList[$key] = '"' . trim($user) . '"';
            }
            $userList = implode(',', $userList);
        }

        if(!$startDate || !$endDate){
            return "{}";
        }

        switch ($id) {

            case 'participation':
                $relations = $this->app['domain.ranking.get_relation_participation']->run($startDate, $endDate, null,$userList);
                break;

            case 'approval':
                $relations = $this->app['domain.ranking.get_relation_approval']->run($startDate, $endDate, null,$userList);
                break;

            case 'changes':
                $relations = $this->app['domain.ranking.get_relation_changes']->run($startDate, $endDate, null,$userList);
                break;
            default: return new NotFoundHttpException();
        }

        $type = null;
        if($id === 'changes'){
            $type = 'changes_requested';
        }
        if($id === 'approval'){
            $type = 'approved';
        }
        $users = $this->app['user.repository']->getAllByDate($startDate, $endDate, $userList, $type);


        return json_encode(['nodes' => $users, 'relations' => $relations]);
    }

    public function show($id, Request $request)
    {
        $start = (new \DateTime('-1 month'))->format('Y-m-d');
        $end = (new \DateTime())->format('Y-m-d');

        $startDate = ($request->get('startDate')) ? $request->get('startDate') : $start;
        $endDate =  ($request->get('endDate')) ? $request->get('endDate') : $end;
        $userList = $request->get('userlist');

        return $this->app['twig']->render('interactions/graph.html.twig',
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'id' => $id,
                'userlist' => $userList,
            ]
        );
    }
}