<?php

namespace ReviewHeroes\Repositories;

use ReviewHeroes\Domain\Notification\NotificationRepository as NotificationRepositoryInterface;

class NotificationRepository implements NotificationRepositoryInterface
{
    const CACHE_PREFIX = 'notifications';

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

    public function addNewSubscription(string $userEmail)
    {
        $query = "
            INSERT INTO Subscriptions (email)
            VALUES (:email)
        ";

        $query = $this->db->prepare($query);

        $query->bindParam(':email', $userEmail);

        try {
            $query->execute();
        } catch (\PDOException $e) {
            // Duplicated entry
            if ($e->getCode() == 23000) {
                throw new \InvalidArgumentException("Email $userEmail already exists");
            }

            throw new \InvalidArgumentException($e->getMessage());
        }

    }

    public function getSubscribedTotal(): int
    {
        $query = "SELECT COUNT(*) FROM Subscriptions";

        $query = $this->db->prepare($query);
        $query->execute();
        $result = $query->fetch();
        return $result[0];
    }

    public function getAll(): array
    {
        $query = "SELECT * FROM Subscriptions";

        $query = $this->db->query($query);
        return $query->fetchAll();
    }
}