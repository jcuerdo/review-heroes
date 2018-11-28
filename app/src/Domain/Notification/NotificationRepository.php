<?php

namespace ReviewHeroes\Domain\Notification;

interface NotificationRepository
{
    public function addNewSubscription(string $userEmail);
    public function getSubscribedTotal(): int;
    public function getAll(): array;
}