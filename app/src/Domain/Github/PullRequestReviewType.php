<?php

namespace ReviewHeroes\Domain\Github;

class PullRequestReviewType
{
    private $id;
    private $type;
    private $title;
    private $url;
    private $reviewState;
    private $pullState;
    private $comment;
    private $owner;

    /**
     * PullRequestReviewType constructor.
     * @param $id
     * @param $type
     * @param $title
     * @param $url
     * @param $reviewState
     * @param $pullState
     * @param $comment
     * @param $owner
     */
    public function __construct(
        $id,
        $type,
        $title,
        $url,
        $reviewState,
        $pullState,
        Comment $comment,
        Author $owner
    )
    {
        $this->id = $id;
        $this->type = $type;
        $this->title = $title;
        $this->url = $url;
        $this->reviewState = $reviewState;
        $this->pullState = $pullState;
        $this->comment = $comment;
        $this->owner = $owner;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return mixed
     */
    public function getReviewState()
    {
        return $this->reviewState;
    }

    /**
     * @return mixed
     */
    public function getPullState()
    {
        return $this->pullState;
    }

    /**
     * @return Comment
     */
    public function getComment()
    {
        return $this->comment;
    }
    /**
     * @return Author
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}