<?php

namespace ReviewHeroes\Domain\Ranking;

use ReviewHeroes\Domain\Participation\ParticipationRepository as ParticipationRepositoryInterface;

class GetApprovalParticipation
{
    private $participationRepository;

    public function __construct(
        ParticipationRepositoryInterface $participationRepository
    )
    {
        $this->participationRepository = $participationRepository;
    }

    public function run($startDate, $endDate, $limit = null) {
        return $this->participationRepository->approvalParticipation($startDate, $endDate, $limit);
    }
}