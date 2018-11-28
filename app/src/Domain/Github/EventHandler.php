<?php

namespace ReviewHeroes\Domain\Github;

use ReviewHeroes\Domain\Build\BuildRepository as BuildRepositoryInterface;
use ReviewHeroes\Domain\Participation\ParticipationRepository as ParticipationRepositoryInterface;
use ReviewHeroes\Domain\User\UserRepository as UserRepositoryInterface;
use Symfony\Component\HttpFoundation\Response;

class EventHandler
{
    private $buildRepository;
    private $participationRepository;
    private $userRepository;
    private $github;

    public function __construct(
        ParticipationRepositoryInterface $participationRepository,
        BuildRepositoryInterface $buildRepository,
        UserRepositoryInterface $userRepository,
        GithubRepository $githubRepository
    )
    {
        $this->buildRepository = $buildRepository;
        $this->participationRepository = $participationRepository;
        $this->userRepository = $userRepository;
        $this->github = $githubRepository;
    }

    public function handle($type, $content)
    {
        switch ($type) {

            case 'status': return $this->handleStatus($content);
            case 'pull_request_review': return $this->handlePullRequestReview($type, $content);
            case 'pull_request_review_comment': return $this->handlePullRequestReviewComment($type, $content);
            case 'issue_comment': return $this->handleIssueComment($type, $content);

            default: return new Response('Event discarded' , 400);
        }
    }

    private function handleStatus($content)
    {
        if ($content['state'] != 'pending') {

            $author = new Author(
                $content['commit']['author']['id'],
                $content['commit']['author']['login'],
                $content['commit']['author']['avatar_url']
            );

            $statusTypeEvent = new RepositoryStatusType(
                $content['state'],
                $content['sha'],
                $content['target_url'],
                $content['context'],
                $content['description'],
                new Commit($author)
            );

            $this->buildRepository->insert($statusTypeEvent);
            $this->userRepository->insert($author);

            return new Response('OK');

        } else {

            return new Response('Status pending, discard', 400);

        }
    }

    private function handlePullRequestReview($type, $content)
    {
        $author = new Author(
            $content['pull_request']['user']['id'],
            $content['pull_request']['user']['login'],
            $content['pull_request']['user']['avatar_url']
        );

        $reviewer = new Author(
            $content['review']['user']['id'],
            $content['review']['user']['login'],
            $content['review']['user']['avatar_url']
        );

        $pullRequestReview = new PullRequestReviewType(
            $content['pull_request']['id'],
            $type,
            $content['pull_request']['title'],
            $content['pull_request']['html_url'],
            isset($content['review']['state']) ? $content['review']['state'] : '',
            $content['pull_request']['state'],
            new Comment($reviewer),
            $author
        );

        $this->userRepository->insert($author);
        $this->userRepository->insert($reviewer);
        $this->participationRepository->insert($pullRequestReview);

        return new Response('OK');
    }

    private function handlePullRequestReviewComment($type, $content)
    {
        $reviewer = new Author(
            $content['comment']['user']['id'],
            $content['comment']['user']['login'],
            $content['comment']['user']['avatar_url']
        );

        $author = new Author(
            $content['pull_request']['user']['id'],
            $content['pull_request']['user']['login'],
            $content['pull_request']['user']['avatar_url']
        );

        $pullRequestReview = new PullRequestReviewType(
            $content['pull_request']['id'],
            $type,
            $content['pull_request']['title'],
            $content['pull_request']['html_url'],
            isset($content['review']['state']) ? $content['review']['state'] : '',
            $content['pull_request']['state'],
            new Comment($reviewer),
            $author
        );

        $this->userRepository->insert($author);
        $this->userRepository->insert($reviewer);
        $this->participationRepository->insert($pullRequestReview);

        return new Response('OK');
    }

    private function handleIssueComment($type, $content)
    {
        $resultContent = $this->github->get($content['issue']['pull_request']['url']);

        $body = $content['comment']['body'];

        if (strpos($body, 'retest ') !== false) {
            return new Response('Not valid, is Retest');
        }

        $reviewer = new Author(
            $content['comment']['user']['id'],
            $content['comment']['user']['login'],
            $content['comment']['user']['avatar_url']
        );

        $author = new Author(
            $resultContent['user']['id'],
            $resultContent['user']['login'],
            $resultContent['user']['avatar_url']
        );

        $pullRequestReview = new PullRequestReviewType(
            $resultContent['id'],
            $type,
            isset($content['issue']['pull_request']['title']) ? $content['issue']['pull_request']['title'] : $content['issue']['title'],
            $content['issue']['pull_request']['html_url'],
            '',
            $resultContent['state'],
            new Comment($reviewer),
            $author
        );

        $this->userRepository->insert($author);
        $this->userRepository->insert($reviewer);
        $this->participationRepository->insert($pullRequestReview);

        return new Response('OK');
    }
}