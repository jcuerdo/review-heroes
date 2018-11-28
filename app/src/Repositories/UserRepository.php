<?php

namespace ReviewHeroes\Repositories;

use ReviewHeroes\Domain\Github\Author;
use ReviewHeroes\Domain\User\UserRepository as UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface {

    const CACHE_PREFIX = 'users';

    private $db;
    private $redis;

    public function __construct(
        \PDO $db,
        \Redis $redis
    ) {
        $this->db = $db;
        $this->redis = $redis;
    }

    public function insert(Author $author)
    {
        $this->redis->delete($this->redis->keys(self::CACHE_PREFIX . '_*'));

        $query = "
            INSERT IGNORE INTO Users (id, username, avatar_url)
            VALUES (:id, :username, :avatar_url)
        ";

        $query = $this->db->prepare($query);

        $id = $author->getId();
        $username = $author->getLogin();
        $avatar_url = $author->getAvatarUrl();

        $query->bindParam(':id', $id);
        $query->bindParam(':username', $username);
        $query->bindParam(':avatar_url', $avatar_url);

        return $query->execute();
    }

    public function getUser($id)
    {
        $cacheKey = self::CACHE_PREFIX . "_$id";

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $query = "
            SELECT *
            FROM 
              Users
            WHERE
              id = :id
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $result = $query->fetch();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function getStats($id)
    {
        $cacheKey = self::CACHE_PREFIX . "_stats_$id";

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $query = "
            SELECT COUNT(*) as total
            FROM 
              Users
            JOIN Participations ON Participations.UserId = Users.id
            WHERE Users.id = :id
            AND Participations.reviewState NOT IN ('changes_requested', 'approved')
            AND Participations.userId != Participations.pullRequestOwnerId
            GROUP BY Participations.userId
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $comments_count = $query->fetch();

        $query = "
            SELECT COUNT(*) as approved_count
            FROM 
              Users
            JOIN Participations ON Participations.UserId = Users.id
            WHERE Users.id = :id
            AND Participations.reviewState = 'approved'
            AND Participations.pullRequestOwnerId != :id
            GROUP BY Participations.userId
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $approved_count = $query->fetch();

        $query = "
            SELECT COUNT(*) as pickies_count
            FROM 
              Users
            JOIN Participations ON Participations.UserId = Users.id
            WHERE Users.id = :id
            AND Participations.reviewState = 'changes_requested'
            AND Participations.pullRequestOwnerId != :id
            GROUP BY Participations.userId
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $pickies_count = $query->fetch();

        $query = "
            SELECT COUNT(*) as not_reviewed_count
            FROM 
              Participations
            LEFT JOIN Users ON Participations.UserId = Users.id
            WHERE Users.id IS NULL
            AND Participations.pullState = 'closed'
            AND Participations.pullRequestOwnerId != :id
            GROUP BY Participations.userId
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $not_reviewed_count = $query->fetch();

        $result =  [
            'total' => $comments_count['total'] ?: 0,
            'approved_count' => $approved_count['approved_count'] ?: 0,
            'pickies_count' => $pickies_count['pickies_count'] ?: 0,
            'not_reviewed_count' => $not_reviewed_count['not_reviewed_count'] ?: 0
        ];

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function getUserPullRequests($id)
    {
        $cacheKey = self::CACHE_PREFIX . "_pull_request_$id";

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $query = "
            SELECT 
              DISTINCT(Participations.pullRequestId), 
              pullState, 
              pullRequestTitle,
              reviewState, 
              creationDate, 
              Users.avatar_url, 
              Users.username,
              Users.id,
              pullRequestUrl,
              pullRequestOwnerId
            FROM 
              Participations
            JOIN Users ON Participations.pullRequestOwnerId = Users.id
            WHERE Participations.userId = :id
            AND Participations.pullRequestOwnerId != :id
            AND creationDate IN (
                SELECT MAX(creationDate ) 
                FROM Participations
                WHERE Participations.userId = :id
                AND Participations.pullRequestOwnerId != :id
                GROUP BY Participations.pullRequestId, Participations.userId
              )
            ORDER BY creationDate DESC
            LIMIT 6
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function getAll()
    {
        $cacheKey = self::CACHE_PREFIX . '_all';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $query = "
            SELECT * FROM Users ORDER BY username ASC
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function getAllByDate($startDate, $endDate, $userList = "", $type = null)
    {
        $cacheKey = self::CACHE_PREFIX . '_all_by_date';
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($userList) ? '_' . $userList : '';
        $cacheKey .= ($type) ? '_' . $type : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterWhere = 'WHERE 1';

        if($startDate && $endDate){
            $filterWhere .= " AND (DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate') ";
        }

        if($userList){
            $filterWhere .= " AND  Users.username in ($userList) ";
        }

        if($type){
            $filterWhere .= " AND  Participations.reviewState = '$type' ";
        }

        $query = "
            SELECT Users.id , count(Participations.userId) as participations,Users.username, Users.avatar_url
            FROM Users 
            LEFT JOIN Participations
            ON Users.id = Participations.userId or Users.id = Participations.pullRequestOwnerId
            $filterWhere
            GROUP by Users.id;
            HAVING participations > 0
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }
}