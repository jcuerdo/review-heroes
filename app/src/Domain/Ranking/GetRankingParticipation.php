<?php

namespace ReviewHeroes\Domain\Ranking;

use ReviewHeroes\Domain\Participation\ParticipationRepository as ParticipationRepositoryInterface;

class GetRankingParticipation
{
    private $participationRepository;

    public function __construct(
        ParticipationRepositoryInterface $participationRepository
    )
    {
        $this->participationRepository = $participationRepository;
    }

    public function run($startDate, $endDate, $limit = null) {
        return $this->participationRepository->rankingParticipation($startDate, $endDate, $limit);
    }
}