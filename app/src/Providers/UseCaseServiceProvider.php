<?php

namespace ReviewHeroes\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use ReviewHeroes\Domain\Github\EventHandler;
use ReviewHeroes\Domain\Ranking\GetApprovalParticipation;
use ReviewHeroes\Domain\Ranking\GetChangesParticipation;
use ReviewHeroes\Domain\Notification\BroadcastStats;
use ReviewHeroes\Domain\Ranking\GetRankingApprovers;
use ReviewHeroes\Domain\Ranking\GetRankingBuildFailures;
use ReviewHeroes\Domain\Ranking\GetRankingCommenters;
use ReviewHeroes\Domain\Ranking\GetRankingParticipation;
use ReviewHeroes\Domain\Ranking\GetRankingPendingReviews;
use ReviewHeroes\Domain\Ranking\GetRankingPickies;
use ReviewHeroes\Domain\Ranking\GetRelationParticipation;
use ReviewHeroes\Domain\User\GetUserBuildStats;
use ReviewHeroes\Domain\User\GetUserProfile;
use ReviewHeroes\Domain\User\GetUserProfileStats;

class UseCaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['domain.user.get_user_profile'] = function($app) {
            return new GetUserProfile(
                $app['user.repository'],
                $app['build.repository']
            );
        };

        $app['domain.user.get_user_profile_stats'] = function($app) {
            return new GetUserProfileStats(
                $app['participations.repository']
            );
        };

        $app['domain.user.get_user_build_stats'] = function($app) {
            return new GetUserBuildStats(
                $app['build.repository']
            );
        };

        $app['domain.github.event_handler'] = function($app) {
            return new EventHandler(
                $app['participations.repository'],
                $app['build.repository'],
                $app['user.repository'],
                $app['github.repository']
            );
        };

        $app['domain.ranking.get_ranking_participation'] = function($app) {
            return new GetRankingParticipation($app['participations.repository']);
        };

        $app['domain.ranking.get_ranking_approvers'] = function($app) {
            return new GetRankingApprovers($app['participations.repository']);
        };

        $app['domain.ranking.get_ranking_pickies'] = function($app) {
            return new GetRankingPickies($app['participations.repository']);
        };

        $app['domain.ranking.get_ranking_pending_reviews'] = function($app) {
            return new GetRankingPendingReviews($app['participations.repository']);
        };

        $app['domain.ranking.get_ranking_build_failures'] = function($app) {
            return new GetRankingBuildFailures($app['build.repository']);
        };

        $app['domain.ranking.get_ranking_commenters'] = function($app) {
            return new GetRankingCommenters($app['participations.repository']);
        };

        $app['domain.notification.broadcast_stats_by_email'] = function($app) {
            return new BroadcastStats(
                $app['notification.repository'],
                $app['email_notifier'],
                $app['config']['notifications']['email']['from'],
                $app['config']['notifications']['email']['title'],
                'Review Heroes Stats!'
            );
        };

        $app['domain.notification.broadcast_stats_by_email'] = function($app) {
            return new BroadcastStats(
                $app['notification.repository'],
                $app['email_notifier'],
                $app['config']['notifications']['email']['from'],
                $app['config']['notifications']['email']['title'],
                'Monthly Review Awards'
            );
        };

        $app['domain.ranking.get_relation_participation'] = function($app) {
            return new GetRelationParticipation($app['participations.repository']);
        };

        $app['domain.ranking.get_relation_approval'] = function($app) {
            return new GetApprovalParticipation($app['participations.repository']);
        };
        $app['domain.ranking.get_relation_changes'] = function($app) {
            return new GetChangesParticipation($app['participations.repository']);
        };
    }
}