<?php

namespace ReviewHeroes\Domain\User;

use ReviewHeroes\Domain\Github\Author;

interface UserRepository
{
    public function insert(Author $author);
    public function getUser($id);
    public function getStats($id);
    public function getUserPullRequests($id);
}