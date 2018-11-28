<?php

namespace ReviewHeroes\Domain\Github;

class RepositoryStatusType
{
    private $state;
    private $sha;
    private $target_url;
    private $context;
    private $description;
    private $commit;

    /**
     * RepositoryStatusType constructor.
     * @param $state
     * @param $sha
     * @param $target_url
     * @param $context
     * @param $description
     * @param $commit
     */
    public function __construct(
        $state,
        $sha,
        $target_url,
        $context,
        $description,
        Commit $commit
    )
    {
        $this->state = $state;
        $this->sha = $sha;
        $this->target_url = $target_url;
        $this->context = $context;
        $this->description = $description;
        $this->commit = $commit;
    }


    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return mixed
     */
    public function getSha()
    {
        return $this->sha;
    }

    /**
     * @return mixed
     */
    public function getTargetUrl()
    {
        return $this->target_url;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getCommit()
    {
        return $this->commit;
    }
}