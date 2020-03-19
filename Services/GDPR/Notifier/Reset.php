<?php
namespace CanalTP\SamCoreBundle\Services\GDPR\Notifier;

use Psr\Log\LogLevel;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class Reset extends Notifier implements HandlerInterface
{
    public function handle(User $user)
    {
        try {
            $user->setDeletionDate(null);
            $this->om->persist($user);
            $this->om->flush();

            $msg = 'deletion date has been unset';
            $this->logActionOnUser($user, $msg, LogLevel::INFO);
        } catch (\Exception $e) {
            $this->logActionOnUser($user, $e->getMessage(), LogLevel::ERROR);
            return false;
        }

        return true;
    }
}
