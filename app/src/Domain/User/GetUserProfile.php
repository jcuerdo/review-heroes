<?php

namespace ReviewHeroes\Domain\User;

use ReviewHeroes\Repositories\BuildRepository;
use ReviewHeroes\Repositories\UserRepository;

class GetUserProfile
{
    private $userRepository;
    private $buildRepository;

    public function __construct(
        UserRepository $userRepository,
        BuildRepository $buildRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->buildRepository = $buildRepository;
    }

    public function run($id)
    {
        return [
            'pr_stats' => $this->userRepository->getStats($id),
            'user' => $this->userRepository->getUser($id),
            'prs' => $this->userRepository->getUserPullRequests($id),
            'build_stats' => $this->buildRepository->getStats($id)
        ];
    }
}