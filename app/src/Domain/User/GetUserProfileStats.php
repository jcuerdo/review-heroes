<?php

namespace ReviewHeroes\Domain\User;

use ReviewHeroes\Domain\Participation\ParticipationRepository;

class GetUserProfileStats
{
    private $participationRepository;

    public function __construct(
        ParticipationRepository $participationRepository
    )
    {
        $this->participationRepository = $participationRepository;
    }

    public function run($id = null, $startDate = null, $endDate = null)
    {
        return $this->participationRepository->getParticipationStats($id, $startDate, $endDate);
    }
}