<?php

namespace ReviewHeroes\Notifiers;

use Maknz\Slack\Client;

class SlackNotifier implements Notifier
{
    /** @var Client  */
    private $client;

    public function __construct(
        Client $client
    )
    {
        $this->client = $client;
    }

    public function send(
        string $from,
        array $to,
        string $content,
        string $title = null,
        string $subject = null,
        string $icon = null
    )
    {
        $this->client
            ->from('@' . $from)
            ->withIcon($icon)
            ->send($content);
    }

    public function getNotifierName(): string
    {
        return 'slack';
    }

}