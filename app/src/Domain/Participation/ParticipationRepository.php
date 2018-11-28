<?php

namespace ReviewHeroes\Domain\Participation;

use ReviewHeroes\Domain\Github\PullRequestReviewType;

interface ParticipationRepository
{
    public function insert(PullRequestReviewType $pullRequestReviewType);
    public function fetchAll();
    public function rankingParticipation($startDate = null, $endDate = null);
    public function rankingApprovers($startDate = null, $endDate = null);
    public function rankingPickies($startDate = null, $endDate = null);
    public function rankingPendingReviews($startDate = null, $endDate = null);
    public function getParticipationStats($id = null, $startDate = null, $endDate = null);
    public function approvalParticipation($startDate = null, $endDate = null, $limit = null, $userList = "");
    public function changesParticipation($startDate = null, $endDate = null, $limit = null, $userList = "");
}