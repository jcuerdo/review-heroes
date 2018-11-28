<?php

namespace ReviewHeroes\Notifiers;

interface Notifier
{
    public function send(
        string $from,
        array $to,
        string $content,
        string $title = null,
        string $subject = null,
        string $icon = null
    );

    public function getNotifierName(): string;
}