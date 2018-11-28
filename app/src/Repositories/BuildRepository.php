<?php

namespace ReviewHeroes\Repositories;

use ReviewHeroes\Domain\Build\BuildRepository as BuildRepositoryInterface;
use ReviewHeroes\Domain\Github\RepositoryStatusType;

class BuildRepository implements BuildRepositoryInterface
{
    const CACHE_PREFIX = 'builds';

    private $db;
    private $redis;

    public function __construct(
        \PDO $db,
        \Redis $redis
    ) {
        $this->db = $db;
        $this->redis = $redis;
    }

    public function insert(
        RepositoryStatusType $repositoryStatusType
    )
    {
        $this->redis->delete($this->redis->keys(self::CACHE_PREFIX . '_*'));

        $query = "
            INSERT INTO Builds (userId, commitId, context, state, description, target_url)
            VALUES (:userId, :commitId, :context, :state, :description, :target_url)
        ";

        $query = $this->db->prepare($query);

        $userId = $repositoryStatusType->getCommit()->getAuthor()->getId();
        $commitId = $repositoryStatusType->getSha();
        $context = $repositoryStatusType->getContext();
        $state = $repositoryStatusType->getState();
        $description = $repositoryStatusType->getDescription();
        $targetUrl = $repositoryStatusType->getTargetUrl();

        $query->bindParam(':userId', $userId);
        $query->bindParam(':commitId', $commitId);
        $query->bindParam(':context', $context);
        $query->bindParam(':state', $state);
        $query->bindParam(':description', $description);
        $query->bindParam(':target_url', $targetUrl);

        return $query->execute();
    }

    public function getStats($id)
    {
        $cacheKey = self::CACHE_PREFIX . "_stats_$id";

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $query = "
            SELECT COUNT(*) as failures_count
            FROM 
              Builds
            JOIN Users ON Builds.UserId = Users.id
            WHERE Users.id = :id
            AND Builds.state = 'failure'
            GROUP BY Builds.UserId
        ";

        $query = $this->db->prepare($query);
        $query->bindParam(':id', $id);
        $query->execute();
        $failures_count = $query->fetch();

        $query = "
            SELECT COUNT(*) as success_count
            FROM 
              Builds
            JOIN Users ON Builds.UserId = Users.id
            WHERE Users.id = :id
            AND Builds.state = 'success'
            GROUP BY Builds.UserId
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':id', $id);

        $query->execute();
        $success_count = $query->fetch();

        $result = [
            'failures_count' => $failures_count['failures_count'] ?: 0,
            'success_count' => $success_count['success_count'] ?: 0
        ];

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function getRankingFailures($startDate = null, $endDate = null, $limit = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_ranking_failures";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = '';
        if($startDate && $endDate){
            $filterDate = " AND (DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate') ";
        }

        $query = "
            SELECT COUNT(Builds.userId) as total,Users.id,Users.username,Users.avatar_url
            FROM 
              Builds
            JOIN Users ON Builds.userId = Users.id
            WHERE Builds.state = 'failure'
            $filterDate
            GROUP BY Builds.userId
            ORDER BY total DESC
        ";

        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->prepare($query);
        $query->bindParam(':id', $id);
        $query->execute();
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;

    }

    public function getBuildStats($id = null, $startDate = null, $endDate = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_build_stats";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($id) ? '_' . $id : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterWhere = 'WHERE 1';

        if ($id) {
            $filterWhere .= " AND userId = $id";
        }

        if($startDate && $endDate){
            $filterWhere .= " AND (DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate') ";
        }

        $query = "
           SELECT 
                DATE(creationDate) as creationDate,
                SUM(IF(state = 'success',1,0)) as build_success,
                SUM(IF(state = 'failure',1,0)) as build_failure
            FROM
                Builds
            $filterWhere
            GROUP BY DATE(creationDate) 
        ";

        $query = $this->db->prepare($query);

        $query->execute();
        $buildStats = $query->fetchAll(\PDO::FETCH_NUM);

        $result = [
            'build_success' => [],
            'build_failure' => [],
            'date' => []
        ];

        foreach($buildStats as $build)
        {
            $result['build_success'][] = $build[1];
            $result['build_failure'][] = $build[2];
            $result['date'][] = $build[0];
        }

        $this->redis->set($cacheKey, $result);

        return $result;
    }
}