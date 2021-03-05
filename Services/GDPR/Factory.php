<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class Factory
{
    const WARNING_AFTER = '5M';

    const DELETE_AFTER = '6M';

    /**
     * @param User $user
     * @param ContainerInterface $container
     * @return HandlerInterface
     */
    public static function create(User $user, ContainerInterface $container)
    {
        //no action for super admin
        if (self::userIsSuperAdmin($user)) {
            return $container->get('sam.gdpr.superadmin.notifier');
        }

        $lastLoginDate = $user->getLastLogin();
        $creationDate = $user->getCreatedAt();
        $deletionDate = $user->getDeletionDate();
        //if user has never been connected, deletion date isn't set and no creation date (DB fixture init)
        if (!$lastLoginDate && !$deletionDate && !$creationDate) {
            return $container->get('sam.gdpr.warning.notifier');
        }

        //if user has never been connected, deletion date isn't set and creation date <= 5 month
        if (!$lastLoginDate && !$deletionDate && self::dateIsBeforeInactivityLimit($creationDate)) {
            return $container->get('sam.gdpr.warning.notifier');
        }

        //if user last login is set and deletion date  isn't set => notify only if last login date <= 5 month
        if ($lastLoginDate && !$deletionDate && self::dateIsBeforeInactivityLimit($lastLoginDate)) {
            return $container->get('sam.gdpr.warning.notifier');
        }

        //if user has never been connected and deletion date is set => remove if deletion date is in past
        if (!$lastLoginDate && $deletionDate && self::dateIsInPast($deletionDate)) {
            return $container->get('sam.gdpr.deletion.notifier');
        }

        //if user has login and deletion dates => if deletion - last login date >= 6 month, then remove them
        //                                        else set deletion date to null
        if ($lastLoginDate && $deletionDate) {
            if (!self::dateDiffGreaterThanLimit($lastLoginDate, $deletionDate)) {
                return $container->get('sam.gdpr.reset.notifier');
            }

            if (self::dateIsInPast($deletionDate)) {
                return $container->get('sam.gdpr.deletion.notifier');
            }
        }

        return $container->get('sam.gdpr.nothing.notifier');
    }

    private static function userIsSuperAdmin(User $user)
    {
        return $user->hasRole('ROLE_SUPER_ADMIN');
    }

    private static function dateIsBeforeInactivityLimit(\DateTime $date)
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P' . self::WARNING_AFTER);
        $lastLoginDateLimit = $now->sub($interval);

        return $date < $lastLoginDateLimit;
    }

    private static function dateIsInPast(\DateTime $date)
    {
        $now = new \DateTime();

        return $date < $now;
    }

    private static function dateDiffGreaterThanLimit(\DateTime $lastLoginDate, \DateTime $deletionDate)
    {
        $endDate = clone $deletionDate;

        $interval = new \DateInterval('P' . self::DELETE_AFTER);
        $dateLimit = $endDate->sub($interval);

        $isGreater = $dateLimit > $lastLoginDate;
        return $isGreater;
    }
}
