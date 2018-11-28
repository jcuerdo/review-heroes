<?php

namespace ReviewHeroes\Domain\Ranking;

use ReviewHeroes\Domain\Participation\ParticipationRepository as ParticipationRepositoryInterface;

class GetRankingPickies
{
    private $participationRepository;

    public function __construct(
        ParticipationRepositoryInterface $participationRepository
    )
    {
        $this->participationRepository = $participationRepository;
    }

    public function run($startDate, $endDate, $limit = null) {
        return $this->participationRepository->rankingPickies($startDate, $endDate, $limit);
    }
}