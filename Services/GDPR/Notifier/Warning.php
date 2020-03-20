<?php

namespace CanalTP\SamCoreBundle\Services\GDPR\Notifier;

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use Symfony\Bundle\TwigBundle\TwigEngine;

class Warning extends Notifier implements HandlerInterface
{
    const DELETING_AFTER = '1M';

    public function handle(User $user)
    {
        $now = new \DateTime();
        $interval = new \DateInterval('P' . self::DELETING_AFTER);
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
            ->setSubject($this->translator->trans('gdpr.deletion.email.subject'))
            ->setFrom('support@kisio.com')
            ->setTo($to)
            ->setReplyTo('support@kisio.com')
            ->setContentType('text/html')
            ->setBody($this->templating->render('CanalTPSamCoreBundle:Email:warning.html.twig', [
                'deletionDate' => $deletionDate
            ]));

        $result = $this->mailer->send($message);

        if ($result === 0) {
            throw new \RuntimeException('Unable to send email to ' . $to);
        }

        $this->logActionOnUser($user, 'warning email has been sent', LogLevel::INFO);
    }
}
