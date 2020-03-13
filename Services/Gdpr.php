<?php

namespace CanalTP\SamCoreBundle\Services;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\RequestStack;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class Gdpr
{
    const INCATIVITY_INTERVAL = '5D';

    const DELETING_AFTER = '1D';

    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TwigEngine
     */
    private $templating;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

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

    public function run()
    {
        $incativeUsers = $this->getIncativeUsers();
        $this->logger->info(sprintf('Found %d inactive users', count($incativeUsers)));
        foreach ($incativeUsers as $user) {
            $this->handleIncativeUser($user);
        }

        return $incativeUsers;
    }

    private function getIncativeUsers()
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P' . self::INCATIVITY_INTERVAL);
        $lastLoginDate = $now->sub($interval);

        return $this->om
            ->getRepository('CanalTPSamEcoreUserManagerBundle:User')
            ->getIncativeUsersSince($lastLoginDate);
    }

    private function handleIncativeUser(User $user)
    {
        if (!$user->getDeletionDate()) {
            $this->notifyUser($user);
        } else {
            $this->handelNotifiedUser($user);
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
            $this->om->flush();

            $msg = 'deletion date has been set to '. $deletionDate->format('c');
            $this->logActionOnUser($user, $msg, LogLevel::INFO);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    private function sendNotificationMail(User $user, \DateTime $deletionDate)
    {
        $to = $user->getEmailCanonical();
        $this->logger->debug('Sending email to ' . $to);

        $message = \Swift_Message::newInstance()
            ->setSubject('subject')
            ->setFrom('info@kisiodigital.com')
            ->setTo($to)
            ->setReplyTo('noreply@kisiodigital.com')
            ->setContentType('text/html')
            ->setBody($this->templating->render('CanalTPSamCoreBundle:Email:gdpr_warning.html.twig', [
                'user' => $user,
                'deletionDate' => $deletionDate
            ]));

        $result = $this->mailer->send($message);

        if ($result === 0) {
            throw new \RuntimeException('Unable to send email to ' . $to);
        }

        $this->logActionOnUser($user, 'alert email has been sent', LogLevel::INFO);
    }

    private function handelNotifiedUser(User $user)
    {
        if (!$this->shouldBeDeleted($user)) {
            $this->logActionOnUser($user, 'no deletion', LogLevel::INFO);
            return;
        }

        $this->deleteUser($user);
    }

    private function shouldBeDeleted(User $user)
    {
        if ($user->hasRole('ROLE_SUPER_ADMIN')) {
            return false;
        }

        $now = new \DateTime();

        if ($now > $user->getDeletionDate()) {
            return true;
        }

        return false;
    }

    private function deleteUser(User $user)
    {
        $this->logActionOnUser($user, 'should be deleted', LogLevel::INFO);
    }

    private function logActionOnUser(User $user, $message, $level)
    {
        $msg = sprintf(
            'Client %s: User ID %s: %s ',
            $user->getCustomer()->getName(),
            $user->getId(),
            $message
        );

        $this->logger->log($level, $msg);
    }
}
