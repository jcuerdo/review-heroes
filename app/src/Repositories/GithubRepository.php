<?php

namespace ReviewHeroes\Repositories;

use Milo\Github\Api;
use ReviewHeroes\Domain\Github\GithubRepository as GithubRepositoryInterface;

class GithubRepository implements GithubRepositoryInterface
{
    private $api;

    public function __construct(
        Api $api
    )
    {
        $this->api = $api;
    }

    public function get($url)
    {
        $result = $this->api->get(parse_url($url)['path']);
        return (json_decode($result->getContent(), true));
    }
}