<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use Psr\Log\LogLevel;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class DeletionNotifier extends Notifier implements HandlerInterface
{
    public function handle(User $user)
    {
        if (!$this->shouldBeDeleted($user)) {
            return;
        }

        $this->deleteUser($user);
    }

    private function shouldBeDeleted(User $user)
    {
        if ($this->userIsSuperAdmin($user)) {
            $this->logActionOnUser($user, 'no deletion : user is super admin', LogLevel::INFO);
            return false;
        }

        $now = new \DateTime();

        if ($now > $user->getDeletionDate()) {
            return true;
        }

        $this->logActionOnUser($user, 'no deletion. Deletion date is in the future', LogLevel::INFO);
        return false;
    }

    private function deleteUser(User $user)
    {
        try {
            $this->sendDeletionMail($user);
            $this->om->remove($user);
            $this->om->flush();
            $this->logActionOnUser($user, 'account has been deleted', LogLevel::INFO);
        } catch (\Exception $e) {
            $this->logActionOnUser($user, $e->getMessage(), LogLevel::ERROR);
        }
    }

    private function sendDeletionMail(User $user)
    {
        $to = $user->getEmailCanonical();
        $this->logger->debug('Sending deletion email to ' . $to);

        $message = \Swift_Message::newInstance()
            ->setSubject('subject')
            ->setFrom('info@kisiodigital.com')
            ->setTo($to)
            ->setReplyTo('noreply@kisiodigital.com')
            ->setContentType('text/html')
            ->setBody($this->templating->render('CanalTPSamCoreBundle:Email:warning.html.twig', [
                'user' => $user
            ]));

        $result = $this->mailer->send($message);

        if ($result === 0) {
            throw new \RuntimeException('Unable to send email to ' . $to);
        }

        $this->logActionOnUser($user, 'deletion email has been sent', LogLevel::INFO);
    }
}