<?php

namespace CanalTP\SamCoreBundle\Services\GDPR\Notifier;

use Psr\Log\LogLevel;
use CanalTP\SamEcoreUserManagerBundle\Entity\User;

class SuperAdmin extends Notifier implements HandlerInterface
{
    public function handle(User $user)
    {
        $this->logActionOnUser($user, 'no action, user is super admin', LogLevel::INFO);
        return false;
    }
}
