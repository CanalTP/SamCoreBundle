<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class Handler
{
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

        $users = $this->om->getRepository('CanalTPSamEcoreUserManagerBundle:User')->findAll();

        $this->logger->info(sprintf('Found %d users', count($users)));
        foreach ($users as $user) {
            $hasBeenHandled = $this->handleUser($user);
            if ($hasBeenHandled) {
                $affectedUsers++;
            }
        }

        return $affectedUsers;
    }

    private function handleUser(User $user)
    {
        $handler = Factory::create($user, $this->container);
        return $handler->handle($user);
    }
}
