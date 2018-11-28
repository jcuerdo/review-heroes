<?php

namespace ReviewHeroes\Domain\User;

use ReviewHeroes\Domain\User\UserRepository as UserRepositoryInterface;

class GetUsersList
{
    private $userRepository;

    public function __construct(
        UserRepositoryInterface $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    public function run()
    {
        return $this->userRepository->getAll();
    }
}