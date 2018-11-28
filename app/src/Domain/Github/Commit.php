<?php

namespace ReviewHeroes\Domain\Github;

class Commit
{
    private $author;

    /**
     * Commit constructor.
     * @param $author
     */
    public function __construct(
        Author $author
    )
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }
}