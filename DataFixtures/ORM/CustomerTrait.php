<?php

namespace CanalTP\SamCoreBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

trait CustomerTrait
{

    public function addCustomerToApplication(ObjectManager $om, $applicationReference, $customerReference, $token)
    {
        $customerApplication = new \CanalTP\NmmPortalBundle\Entity\CustomerApplication();
        $customerApplication->setToken($token);
        $customerApplication->setIsActive(true);
        $customerApplication->setCustomer($this->getReference($customerReference));
        $customerApplication->setApplication($this->getReference($applicationReference));
        $om->persist($customerApplication);
    }

    public function createCustomer(ObjectManager $om, $name, $email, $customerReference, $identifier = null)
    {
        $nav = new \CanalTP\NmmPortalBundle\Entity\NavitiaEntity();
        $nav->setEmail($email);
        $nav->setName($name);
        $om->persist($nav);

        $customer = new \CanalTP\NmmPortalBundle\Entity\Customer();
        $customer->setName($name);
        $customer->setIdentifier(($identifier == null ? $customer->getNameCanonical() : $identifier));
        $customer->setNavitiaEntity($nav);
        $om->persist($customer);
        $this->addReference('customer-' . $customerReference, $customer);
    }

    public function addPerimeterToCustomer(ObjectManager $om, $externalCoverageId, $externalNetworkId, $customerReference)
    {
        $navitiaEntity = $this->getReference($customerReference)->getNavitiaEntity();

        $perimeter = new \CanalTP\NmmPortalBundle\Entity\Perimeter();
        $perimeter->setNavitiaEntity($navitiaEntity);
        $perimeter->setExternalCoverageId($externalCoverageId);
        $perimeter->setExternalNetworkId($externalNetworkId);
        $om->persist($perimeter);
    }
}
