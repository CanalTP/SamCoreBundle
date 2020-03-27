<?php

namespace CanalTP\SamCoreBundle\Services\GDPR\Notifier;

use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use Symfony\Bundle\TwigBundle\TwigEngine;
use CanalTP\SamCoreBundle\Services\GDPR\HandlerInterface;

class Warning extends Notifier implements HandlerInterface
{
    const DELETING_AFTER = '1M';

    private $baseUrl;

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

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

            $msg = 'deletion date has been set to ' . $deletionDate->format('Y-m-d H:i:s');
            $this->logActionOnUser($user, $msg, LogLevel::INFO);
        } catch (\Exception $e) {
            $this->logActionOnUser($user, $e->getMessage(), LogLevel::ERROR);
            return false;
        }

        return true;
    }

    private function sendNotificationMail(User $user, \DateTime $deletionDate)
    {
        $subject = $this->translator->trans('gdpr.warning.email.subject');
        $body = $this->templating->render('CanalTPSamCoreBundle:Email:warning.html.twig', [
            'deletionDate' => $deletionDate,
            'baseUrl' => $this->baseUrl
        ]);
        $this->sendEmailToUser($user, $subject, $body);
    }
}
