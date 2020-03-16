<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\TwigBundle\TwigEngine;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

abstract class Notifier
{
    const DELETING_AFTER = '1D';

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TwigEngine
     */
    protected $templating;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    public function __construct(
        ObjectManager $om,
        LoggerInterface $logger,
        TwigEngine $templating,
        \Swift_Mailer $mailer
    ) {
        $this->om = $om;
        $this->logger = $logger;
        $this->templating = $templating;
        $this->mailer = $mailer;
    }

    protected function userIsSuperAdmin(User $user)
    {
        return $user->hasRole('ROLE_SUPER_ADMIN');
    }

    protected function logActionOnUser(User $user, $message, $level)
    {

        $msg = sprintf(
            'Client %s User ID %s %s ',
            str_pad($user->getCustomer()->getName() . ':', 20, ' '),
            str_pad($user->getId() . ':', 3, ' '),
            $message
        );

        $this->logger->log($level, $msg);
    }
}
