<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class Handler
{
    const INACTIVITY_INTERVAL = '5D';

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ObjectManager $om, LoggerInterface $logger, ContainerInterface $container)
    {
        $this->om = $om;
        $this->logger = $logger;
        $this->container = $container;
    }

    public function run()
    {
        $affectedUsers = 0;

        $inactiveUsers = $this->getInactiveUsers();
        $this->logger->info(sprintf('Found %d inactive users', count($inactiveUsers)));
        foreach ($inactiveUsers as $user) {
            $hasBeenHandled = $this->handleInactiveUser($user);
            if ($hasBeenHandled) {
                $affectedUsers++;
            }
        }

        return $affectedUsers;
    }

    private function getInactiveUsers()
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P' . self::INACTIVITY_INTERVAL);
        $lastLoginDate = $now->sub($interval);

        return $this->om
            ->getRepository('CanalTPSamEcoreUserManagerBundle:User')
            ->getIncativeUsersSince($lastLoginDate);
    }

    private function handleInactiveUser(User $user)
    {
        $handler = Factory::create($user, $this->container);
        return $handler->handle($user);
    }
}
