<?php

namespace ReviewHeroes\Domain\Build;

use ReviewHeroes\Domain\Github\RepositoryStatusType;

interface BuildRepository
{
    public function insert(RepositoryStatusType $repositoryStatusType);
    public function getStats($id);
    public function getRankingFailures($startDate = null, $endDate= null);
    public function getBuildStats($id = null, $startDate = null, $endDate = null);
}