<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use Symfony\Bundle\TwigBundle\TwigEngine;

class WarningNotifier extends Notifier implements HandlerInterface
{
    public function __construct(
        ObjectManager $om,
        LoggerInterface $logger,
        TwigEngine $templating,
        \Swift_Mailer $mailer
    ) {
        parent::__construct($om, $logger, $templating, $mailer);
    }

    public function handle(User $user)
    {
        if ($this->userIsSuperAdmin($user)) {
            $this->logActionOnUser($user, 'no action, user is super admin', LogLevel::INFO);
            return false;
        }

        $now = new \DateTime();
        $interval = new \DateInterval('P' . parent::DELETING_AFTER);
        $deletionDate = $now->add($interval);
        try {
            $this->sendNotificationMail($user, $deletionDate);
            $user->setDeletionDate($deletionDate);
            $this->om->persist($user);
            $this->om->flush();

            $msg = 'deletion date has been set to '. $deletionDate->format('Y-m-d H:i:s');
            $this->logActionOnUser($user, $msg, LogLevel::INFO);
        } catch (\Exception $e) {
            $this->logActionOnUser($user, $e->getMessage(), LogLevel::ERROR);
            return false;
        }

        return true;
    }

    private function sendNotificationMail(User $user, \DateTime $deletionDate)
    {
        $to = $user->getEmailCanonical();
        $this->logger->debug('Sending warning email to ' . $to);

        $message = \Swift_Message::newInstance()
            ->setSubject('subject')
            ->setFrom('info@kisiodigital.com')
            ->setTo($to)
            ->setReplyTo('noreply@kisiodigital.com')
            ->setContentType('text/html')
            ->setBody($this->templating->render('CanalTPSamCoreBundle:Email:warning.html.twig', [
                'user' => $user,
                'deletionDate' => $deletionDate
            ]));

        $result = $this->mailer->send($message);

        if ($result === 0) {
            throw new \RuntimeException('Unable to send email to ' . $to);
        }

        $this->logActionOnUser($user, 'warning email has been sent', LogLevel::INFO);
    }
}
