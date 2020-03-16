<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class Factory
{
    /**
     * @param User $user
     * @param ContainerInterface $container
     * @return HandlerInterface
     */
    public static function create(User $user, ContainerInterface $container)
    {
        if (!$user->getDeletionDate()) {
            return $container->get('sam.gdpr.warning.notifier');
        }

        return $container->get('sam.gdpr.deletion.notifier');
    }
}
