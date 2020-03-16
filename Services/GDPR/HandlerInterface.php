<?php

namespace CanalTP\SamCoreBundle\Services\GDPR;

use CanalTP\SamEcoreUserManagerBundle\Entity\User;

interface HandlerInterface
{
    public function handle(User $user);
}
