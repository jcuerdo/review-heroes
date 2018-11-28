<?php

namespace ReviewHeroes\Providers;

use Aws\AwsClient;
use Aws\Ses\SesClient;
use League\HTMLToMarkdown\HtmlConverter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ReviewHeroes\Notifiers\EmailNotifier;
use ReviewHeroes\Notifiers\SlackNotifier;
use ThreadMeUp\Slack\Client;

class NotificationServiceProvider implements ServiceProviderInterface
{
    public function boot(Container $app)
    {

    }

    public function register(Container $app)
    {
        $app['ses_client'] = function() use ($app) {
            return new SesClient([
                'region'  => $app['config']['aws']['region'],
                'credentials' => [
                    'key'         => $app['config']['aws']['access_key'],
                    'secret'  => $app['config']['aws']['secret']
                ],
                'version' => 'latest'
            ]);
        };

        $app['email_notifier'] = $app->factory(function () use ($app) {
            $mailer = new \PHPMailer();
            $mailer->isSMTP();
            $mailer->Host = $app['config']['notifications']['email']['host'];
            $mailer->SMTPAuth = true;
            $mailer->Username = $app['config']['notifications']['email']['username'];
            $mailer->Password = $app['config']['notifications']['email']['password'];
            $mailer->SMTPSecure = 'tls';
            $mailer->Port = $app['config']['notifications']['email']['port'];

            return new EmailNotifier(
                $app['ses_client'],
                $mailer
            );
        });

        $app['slack_notifier'] = function() use ($app) {

            $settings = [
                'username' => 'review-heroes',
                //'channel' => '#bcn-bestlix-jenkins',
                'link_names' => true,
                'icon' => $app['config']['notifications']['slack']['icon']
            ];

            $client = new \Maknz\Slack\Client(
                $app['config']['notifications']['slack']['webhook_endpoint'],
                $settings
            );

            return new SlackNotifier($client);
        };

        $app['html_to_markdown'] = function() use ($app) {
            return new HtmlConverter(
                [
                    'strip_tags' => true
                ]
            );
        };
    }
}