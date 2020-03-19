<?php

namespace CanalTP\SamCoreBundle\Services\GDPR\Notifier;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;

interface HandlerInterface
{
    public function handle(User $user);
}
