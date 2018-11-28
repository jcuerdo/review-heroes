<?php

namespace ReviewHeroes\Domain\User;

use ReviewHeroes\Repositories\BuildRepository;

class GetUserBuildStats
{
    private $buildRepository;

    public function __construct(
        BuildRepository $buildRepository
    )
    {
        $this->buildRepository = $buildRepository;
    }

    public function run($id = null, $startDate = null, $endDate = null)
    {
        return $this->buildRepository->getBuildStats($id, $startDate, $endDate);
    }
}