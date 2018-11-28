<?php

namespace ReviewHeroes\Domain\Github;

interface GithubRepository {
    public function get($url);
}