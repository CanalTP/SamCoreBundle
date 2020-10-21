<?php

namespace CanalTP\SamCoreBundle\Doctrine;

use CanalTP\SamCoreBundle\Entity\Role;
use CanalTP\SamCoreBundle\Slugify;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;

class RoleListener
{
    private function canonicalize(Role $role)
    {
        $slug = Slugify::format($role->getName(), '_');

        return 'ROLE_' . strtoupper($slug);
    }

    public function preUpdate(Role $role, PreUpdateEventArgs $event)
    {
        $role->setCanonicalName($this->canonicalize($role));
    }

    public function prePersist(Role $role, LifecycleEventArgs $event)
    {
        $role->setCanonicalName($this->canonicalize($role));
    }
}
