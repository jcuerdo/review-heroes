<?php

namespace ReviewHeroes\Domain\Notification;

use ReviewHeroes\Notifiers\Notifier;

class BroadcastStats
{
    /** @var NotificationRepository  */
    private $notificationRepository;

    /** @var  Notifier */
    private $notifier;

    /** @var string  */
    private $from;

    /** @var  string */
    private $title;

    /** @var string  */
    private $subject;

    public function __construct(
        NotificationRepository $notificationRepository,
        Notifier $notifier,
        string $from,
        string $title,
        string $subject
    )
    {
        $this->notificationRepository = $notificationRepository;
        $this->notifier = $notifier;
        $this->from = $from;
        $this->title = $title;
        $this->subject = $subject;
    }

    public function run($content)
    {
        $subscriptions = $this->notificationRepository->getAll();

        $to = $this->getRecipients($subscriptions);

        $this->notifier->send(
            $this->from,
            $to,
            $content,
            $this->title,
            $this->subject
        );
    }

    /**
     * @param $subscriptions
     * @return array
     */
    private function getRecipients($subscriptions): array
    {
        $to = [];
        foreach ($subscriptions as $subscriber) {
            $to[] = $subscriber[$this->notifier->getNotifierName()];
        }
        return $to;
    }
}