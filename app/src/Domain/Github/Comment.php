<?php

namespace ReviewHeroes\Domain\Github;

class Comment
{
    private $author;

    /**
     * Comment constructor.
     * @param $author
     */
    public function __construct($author)
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