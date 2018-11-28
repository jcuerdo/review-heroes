<?php

namespace ReviewHeroes\Notifiers;

use Aws\Ses\SesClient;

class EmailNotifier implements Notifier
{
    /** @var  SesClient */
    private $emailVerifier;
    
    /** @var  \PHPMailer */
    private $mailer;
    
    public function __construct(
        SesClient $emailVerifier,
        \PHPMailer $mailer
    )
    {
        $this->emailVerifier = $emailVerifier;
        $this->mailer = $mailer;
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
        $verifiedEmailAddresses = $this->emailVerifier->listVerifiedEmailAddresses();

        foreach ($to as $address) {
            if ($this->isAddressVerified($address, $verifiedEmailAddresses))
            {
                $this->mailer->setFrom($from, $title);
                $this->mailer->addAddress($address);
                $this->mailer->isHTML(true);
                $this->mailer->Subject = $subject;
                $this->mailer->Body    = $content;

                if(!$this->mailer->send()) {
                    throw new \Exception($this->mailer->ErrorInfo);
                }
            }
        }
    }

    public function getNotifierName(): string
    {
        return 'email';
    }

    private function isAddressVerified($address, $verifiedEmailAddresses): bool
    {
        return in_array($address, ($verifiedEmailAddresses->toArray())['VerifiedEmailAddresses']);
    }
}