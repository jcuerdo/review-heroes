<?php

namespace ReviewHeroes\Domain\Ranking;

use ReviewHeroes\Domain\Participation\ParticipationRepository as ParticipationRepositoryInterface;

class GetRankingCommenters
{
    private $participationRepository;

    public function __construct(
        ParticipationRepositoryInterface $participationRepository
    )
    {
        $this->participationRepository = $participationRepository;
    }

    public function run($startDate, $endDate, $limit = null) {
        return $this->participationRepository->rankingCommenters($startDate, $endDate, $limit);
    }
}