<?php

namespace CanalTP\SamCoreBundle\DataFixtures\ORM;

use CanalTP\SamCoreBundle\Entity\Application;
use Doctrine\Common\Persistence\ObjectManager;

trait ApplicationTrait
{
    /**
     * Crée l'enregistrement dans la table tj_application_role.
     *
     * @param string $name
     * @param string $route
     *
     * @return Application
     */
    public function createApplication(ObjectManager $om, $name, $route, $reference = null, $bundleName = null)
    {
        $canonicalName = strtolower(($reference === null) ? $name : $reference);
        $entity = $om->getRepository('CanalTPSamCoreBundle:Application')->findOneByCanonicalName($canonicalName);
        if (is_null($entity)) {
            $entity = new Application($name);
            $entity->setDefaultRoute($this->cleanRoute($route));
            $entity->setCanonicalName($canonicalName);
            $entity->setBundleName($bundleName);

            $om->persist($entity);
        }

        $this->addReference('app-' . $entity->getCanonicalName(), $entity);
    }

    /**
     * Clean la route
     *
     * @param string $route
     *
     * @return string
     */
    private function cleanRoute($route)
    {
        $cleanedRoute = trim($route);
        // Route must begin with "/"
        if ($cleanedRoute && $cleanedRoute[0] !== '/') {
            $cleanedRoute = '/' . $cleanedRoute;
        }
        // For first component route, must end with "/"
        if ($cleanedRoute && substr_count($cleanedRoute, '/') === 1) {
            $cleanedRoute = $cleanedRoute . '/';
        }
        return $cleanedRoute;
    }
}
