<?php

namespace ReviewHeroes\Controllers;

use ReviewHeroes\Domain\Github\EventHandler;
use Symfony\Component\HttpFoundation\Request;

class GithubEventPayload
{
    private $githubEventHandler;

    public function __construct(
        EventHandler $githubEventHandler
    )
    {
        $this->githubEventHandler = $githubEventHandler;
    }

    public function handleEvent(Request $request)
    {
        return $this->githubEventHandler->handle(
            $request->headers->get('x-github-event'),
            json_decode($request->getContent(), true)
        );
    }
}