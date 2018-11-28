<?php

namespace ReviewHeroes\Domain\Github;

class Author
{
    private $id;
    private $login;
    private $avatar_url;

    /**
     * Author constructor.
     * @param $id
     * @param $login
     * @param $avatar_url
     */
    public function __construct($id, $login, $avatar_url)
    {
        $this->id = $id;
        $this->login = $login;
        $this->avatar_url = $avatar_url;
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
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return mixed
     */
    public function getAvatarUrl()
    {
        return $this->avatar_url;
    }
}