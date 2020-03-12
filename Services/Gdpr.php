<?php

namespace CanalTP\SamCoreBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class Gdpr
{
    const INCATIVITY_INTERNAL = '5D';

    const DELETING_AFTER = '1D';

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ObjectManager $om, LoggerInterface $logger)
    {
        $this->om = $om;
        $this->logger = $logger;
    }

    public function run()
    {
        $incativeUsers = $this->getIncativeUsers();
        $this->logger->info(sprintf('Found %d inactive users.', count($incativeUsers)));
        foreach ($incativeUsers as $user) {
            $this->handleIncativeUser($user);
        }
        return $incativeUsers;
    }

    private function getIncativeUsers()
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P' . self::INCATIVITY_INTERNAL);
        $lastLoginDate = $now->sub($interval);
        return $this->om->getRepository('CanalTPSamEcoreUserManagerBundle:User')
            ->getIncativeUsersSince($lastLoginDate);
    }

    private function handleIncativeUser(User $user)
    {
        if (!$user->getDeletionDate()) {
            $this->notifyUser($user);
        }
    }

    private function notifyUser(User $user)
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P' . self::DELETING_AFTER);
        $deletionDate = $now->add($interval);
        try {
            $this->sendNotificationMail($user, $deletionDate);
            $user->setDeletionDate($deletionDate);
            $this->om->persist($user);
            //$this->om->flush();
            $pattern = 'Client %s: User ID %s: deletion date has been set to %s';
            $this->logger->info(sprintf($pattern, $user->getCustomer()->getName(), $user->getId(), $deletionDate->format('c')));
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function sendNotificationMail(User $user, \DateTime $deletionDate)
    {
        return true;
    }
}
