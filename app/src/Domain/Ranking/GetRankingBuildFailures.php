<?php

namespace ReviewHeroes\Domain\Ranking;

use ReviewHeroes\Domain\Build\BuildRepository as BuildRepositoryInterface;

class GetRankingBuildFailures
{
    private $buildRepository;

    public function __construct(
        BuildRepositoryInterface $buildRepository
    )
    {
        $this->buildRepository = $buildRepository;
    }

    public function run($startDate, $endDate, $limit = null) {
        return $this->buildRepository->getRankingFailures($startDate, $endDate, $limit);
    }
}