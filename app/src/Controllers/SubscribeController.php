<?php

namespace ReviewHeroes\Controllers;

use Aws\Ses\SesClient;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SubscribeController
{
    /** @var Application  */
    private $app;

    public function __construct(
        Application $app
    )
    {
        $this->app = $app;
    }

    public function getAll()
    {
        return $this->app['twig']->render(
            'subscribe/subscribe.html.twig',
            [
                'subscribe_stats' => [
                    'subscribed_total' => $this->app['notification.repository']->getSubscribedTotal()
                ]
            ]
        );
    }

    public function submit(Request $request)
    {
        $userEmail = $request->get('user_email');
        $securityToken = $request->get('security_token');

        if ($this->app['config']['notifications']['security_token'] !== $securityToken) {
            return $this->app['twig']->render(
                'subscribe/subscribe.html.twig',
                [
                    'errors' => [
                        'Invalid Security Token'
                    ],
                    'subscribe_stats' => [
                        'subscribed_total' => $this->app['notification.repository']->getSubscribedTotal()
                    ]
                ]
            );
        }

        try {

            $this->app['notification.repository']->addNewSubscription($userEmail);

        } catch (\InvalidArgumentException $e) {
            return $this->app['twig']->render(
                'subscribe/subscribe.html.twig',
                [
                    'errors' => [
                        $e->getMessage()
                    ],
                    'subscribe_stats' => [
                        'subscribed_total' => $this->app['notification.repository']->getSubscribedTotal()
                    ]
                ]
            );
        }

        $this->sendEmailVerification($userEmail);

        return $this->app['twig']->render(
            'subscribe/subscribe.html.twig',
            [
                'success' => 'Thank you for your submission! We have sent a verification email to your inbox',
                'subscribe_stats' => [
                    'subscribed_total' => $this->app['notification.repository']->getSubscribedTotal()
                ]

            ]
        );
    }

    /**
     * @param $userEmail
     */
    private function sendEmailVerification($userEmail): void
    {
        /** @var SesClient $sesClient */
        $sesClient = $this->app['ses_client'];

        $verifiedEmailAddresses = $sesClient->listVerifiedEmailAddresses();

        if (!in_array($userEmail, ($verifiedEmailAddresses->toArray())['VerifiedEmailAddresses'])) {
            $sesClient->verifyEmailIdentity([
                'EmailAddress' => $userEmail
            ]);
        }
    }

}