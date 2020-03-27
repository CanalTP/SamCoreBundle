<?php

namespace CanalTP\SamCoreBundle\Services\GDPR\Notifier;

use Psr\Log\LogLevel;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;
use CanalTP\SamCoreBundle\Services\GDPR\HandlerInterface;

class Deletion extends Notifier implements HandlerInterface
{
    private $baseUrl;

    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function handle(User $user)
    {
        try {
            $originalUser = clone $user;
            $this->sendDeletionMail($user);
            $this->om->remove($user);
            $this->om->flush();
            $this->logActionOnUser($originalUser, 'account has been deleted', LogLevel::INFO);
        } catch (\Exception $e) {
            $this->logActionOnUser($user, $e->getMessage(), LogLevel::ERROR);
            return false;
        }

        return true;
    }

    private function sendDeletionMail(User $user)
    {
        $subject = $this->translator->trans('gdpr.deletion.email.subject');
        $body = $this->templating->render('CanalTPSamCoreBundle:Email:deletion.html.twig', [
            'baseUrl' => $this->baseUrl
        ]);
        $this->sendEmailToUser($user, $subject, $body);
    }
}
