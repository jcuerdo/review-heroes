<?php

namespace ReviewHeroes\Repositories;

use ReviewHeroes\Domain\Github\PullRequestReviewType;
use ReviewHeroes\Domain\Participation\ParticipationRepository as ParticipationRepositoryInterface;

class ParticipationsRepository implements ParticipationRepositoryInterface {

    const CACHE_PREFIX = 'participations';

    /* @var \PDO */
    private $db;

    /* @var \Redis */
    private $redis;

    public function __construct(
        \PDO $db,
        \Redis $redis
    ) {
        $this->db = $db;
        $this->redis = $redis;
    }

    public function insert(PullRequestReviewType $pullRequestReviewType)
    {
        $this->redis->delete($this->redis->keys(self::CACHE_PREFIX . '_*'));

        $query = "
            INSERT INTO Participations (pullRequestId,pullRequestOwnerId, pullRequestUrl, pullRequestTitle, userId, pullState,type,reviewState)
            VALUES (:pullRequestId,:pullRequestOwnerId, :pullRequestUrl, :pullRequestTitle, :userId, :pullState,:type,:reviewState)
        ";

        $query = $this->db->prepare($query);

        $pullRequestId = $pullRequestReviewType->getId();
        $pullRequestOwnerId = $pullRequestReviewType->getOwner()->getId();
        $userId = $pullRequestReviewType->getComment()->getAuthor()->getId();
        $pullState = $pullRequestReviewType->getPullState();
        $type = $pullRequestReviewType->getType();
        $reviewState = $pullRequestReviewType->getReviewState();
        $pullRequestUrl = $pullRequestReviewType->getUrl();
        $pullRequestReviewTitle = $pullRequestReviewType->getTitle();

        $query->bindParam(':pullRequestId', $pullRequestId);
        $query->bindParam(':pullRequestOwnerId', $pullRequestOwnerId);
        $query->bindParam(':userId', $userId);
        $query->bindParam(':pullState', $pullState);
        $query->bindParam(':type', $type);
        $query->bindParam(':reviewState', $reviewState);
        $query->bindParam(':pullRequestUrl', $pullRequestUrl);
        $query->bindParam(':pullRequestTitle', $pullRequestReviewTitle);

        return $query->execute();
    }

    public function fetchAll()
    {
        $cacheKey = self::CACHE_PREFIX . '_fetch_all';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $query = "
            SELECT * 
            FROM 
              Participations
            JOIN 
              Users ON Users.id = Participations.userId
        ";

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function relationParticipation($startDate = null, $endDate = null, $limit = null, $userList = "")
    {
        $cacheKey = self::CACHE_PREFIX . "_relation_participation";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = 'WHERE 1';
        if($startDate && $endDate){
            $filterDate .= " AND DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate' ";
        }

        if($userList){
            $filterWhere .= " AND  Users.username in ($userList)";
        }

        $query = "
            SELECT DISTINCT userId as userFrom, pullRequestOwnerId as userTo, count(*) as value
            FROM 
              Participations
            $filterDate
            AND userId != pullRequestOwnerId
            GROUP BY userFrom, userTo
        ";



        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function approvalParticipation($startDate = null, $endDate = null, $limit = null, $userList = "")
    {
        $cacheKey = self::CACHE_PREFIX . "_approval_participation";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = 'WHERE 1';
        if($startDate && $endDate){
            $filterDate .= " AND DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate' ";
        }

        if($userList){
            $filterWhere .= " AND  Users.username in ($userList)";
        }

        $query = "
            SELECT DISTINCT userId as userFrom, pullRequestOwnerId as userTo, count(*) as value
            FROM 
              Participations
            $filterDate
            AND userId != pullRequestOwnerId
            AND reviewState = 'approved'
            GROUP BY userFrom, userTo
        ";



        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function changesParticipation($startDate = null, $endDate = null, $limit = null, $userList = "")
    {
        $cacheKey = self::CACHE_PREFIX . "_changes_participation";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = 'WHERE 1';
        if($startDate && $endDate){
            $filterDate .= " AND DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate' ";
        }

        if($userList){
            $filterWhere .= " AND  Users.username in ($userList)";
        }

        $query = "
            SELECT DISTINCT userId as userFrom, pullRequestOwnerId as userTo, count(*) as value
            FROM 
              Participations
            $filterDate
            AND userId != pullRequestOwnerId
            AND reviewState = 'changes_requested'
            GROUP BY userFrom, userTo
        ";



        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function rankingParticipation($startDate = null, $endDate = null, $limit = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_ranking_participation";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = 'WHERE 1';
        if($startDate && $endDate){
            $filterDate .= " AND DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate' ";
        }

        $query = "
            SELECT count(Participations.userId) as total,Users.username, Users.avatar_url,Users.id            FROM 
              Participations
            JOIN 
              Users ON Users.id = Participations.userId
            $filterDate
            AND Participations.userId != Participations.pullRequestOwnerId
            GROUP BY Participations.userId
            ORDER BY total DESC 
        ";

        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function rankingCommenters($startDate = null, $endDate = null, $limit = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_ranking_comments";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = 'WHERE 1';
        if($startDate && $endDate){
            $filterDate .= " AND DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate' ";
        }

        $query = "
            SELECT count(Participations.userId) as total,Users.username, Users.avatar_url,Users.id            FROM 
              Participations
            JOIN 
              Users ON Users.id = Participations.userId
            $filterDate
            AND Participations.reviewState NOT IN ('changes_requested', 'approved')
            AND Participations.userId != Participations.pullRequestOwnerId
            GROUP BY Participations.userId
            ORDER BY total DESC 
        ";

        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }


    public function rankingApprovers($startDate = null, $endDate = null, $limit = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_ranking_approvers";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = '';
        if($startDate && $endDate){
            $filterDate .= " AND (DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate') ";
        }

        $query = "
            SELECT count(Participations.userId) as total,Users.username, Users.avatar_url,Users.id 
            FROM 
              Participations
            JOIN 
              Users ON Users.id = Participations.userId

            WHERE Participations.reviewState = 'approved'

            $filterDate
            GROUP BY Participations.userId
            ORDER BY total DESC 
        ";

        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }



    public function rankingPickies($startDate = null, $endDate = null, $limit = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_ranking_pickies";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = '';
        if($startDate && $endDate){
            $filterDate .= " AND (DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate') ";
        }

        $query = "
            SELECT count(Participations.userId) as total,Users.username, Users.avatar_url,Users.id 
            FROM 
              Participations
            JOIN 
              Users ON Users.id = Participations.userId

            WHERE Participations.reviewState = 'changes_requested'
            $filterDate
            GROUP BY Participations.userId
            ORDER BY total DESC 
        ";


        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function pendingReviews($startDate = null, $endDate = null, $limit = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_pending_reviews";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = '';
        if($startDate && $endDate){
            $filterDate .= " AND (creationDate >= '$startDate' AND creationDate <= '$endDate') ";
        }

        $query = "
            SELECT DISTINCT
              Users.id,
              Users.username,
              Users.avatar_url,
              mainParticipation.pullRequestUrl
            FROM 
              Participations as mainParticipation
            JOIN 
              Users ON Users.id = mainParticipation.userId
            WHERE type != 'pull_request_review'
            AND mainParticipation.userId != mainParticipation.pullRequestOwnerId
            AND mainParticipation.pullRequestId NOT IN (
              SELECT reviewParticipation.pullRequestId
                from Participations reviewParticipation
                WHERE type = 'pull_request_review'
                AND (
                reviewState = 'approved'
                OR 
                reviewState = 'changes_requested'
                )
                AND mainParticipation.pullRequestId = reviewParticipation.pullRequestId
                AND mainParticipation.userId = reviewParticipation.userId
            )
            $filterDate 
        ";

        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }


    public function rankingPendingReviews($startDate = null, $endDate = null, $limit = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_ranking_pending_reviews";
        $cacheKey .= ($startDate) ? '_' . $startDate : '';
        $cacheKey .= ($endDate) ? '_' . $endDate : '';
        $cacheKey .= ($limit) ? '_' . $limit : '';

        if ($this->redis->exists($cacheKey))
        {
            return $this->redis->get($cacheKey);
        }

        $filterDate = '';
        if($startDate && $endDate){
            $filterDate .= " AND (DATE(creationDate) >= '$startDate' AND DATE(creationDate) <= '$endDate') ";
        }

        $query = "
            SELECT COUNT(DISTINCT(mainParticipation.pullRequestId)) as total, Users.id,Users.username,Users.avatar_url,Users.id 
            FROM 
              Participations as mainParticipation
            JOIN 
              Users ON Users.id = mainParticipation.userId
            WHERE type != 'pull_request_review'
            AND mainParticipation.userId != mainParticipation.pullRequestOwnerId
            AND mainParticipation.pullRequestId NOT IN (
              SELECT reviewParticipation.pullRequestId
                from Participations reviewParticipation
                WHERE type = 'pull_request_review'
                AND (
                reviewState = 'approved'
                OR 
                reviewState = 'changes_requested'
                )
                AND mainParticipation.pullRequestId = reviewParticipation.pullRequestId
                AND mainParticipation.userId = reviewParticipation.userId
            )
            $filterDate
            GROUP BY mainParticipation.userId
            ORDER BY total DESC 
        ";

        if ($limit) {
            $query .= "LIMIT $limit";
        }

        $query = $this->db->query($query);
        $result = $query->fetchAll();

        $this->redis->set($cacheKey, $result);

        return $result;
    }

    public function getParticipationStats($id = null, $startDate = null, $endDate = null)
    {
        $cacheKey = self::CACHE_PREFIX . "_ranking_participation_stats";
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
              COUNT(DATE(creationDate)) as participation_count, DATE(creationDate) as creationDate
            FROM
             Participations
            $filterWhere
            AND Participations.userId != Participations.pullRequestOwnerId
            GROUP BY DATE(creationDate)
        ";

        $query = $this->db->prepare($query);

        $query->execute();
        $participationStats = $query->fetchAll(\PDO::FETCH_NUM);

        $result = [
            'count' => [],
            'date' => []
        ];

        foreach($participationStats as $participation)
        {
            $result['count'][] = $participation[0];
            $result['date'][] = $participation[1];
        }

        $this->redis->set($cacheKey, $result);

        return $result;
    }
}