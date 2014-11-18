<?php

namespace CanalTP\SamCoreBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;

trait CustomerTrait
{
    public function addCustomerToApplication(ObjectManager $om, $applicationReference, $customerReference)
    {
        $customerApplication = new \CanalTP\NmmPortalBundle\Entity\CustomerApplication();
        $customerApplication->setToken('aa01b6b4-2f8f-45dd-9c72-be3c4e2a8681');
        $customerApplication->setIsActive(true);
        $customerApplication->setCustomer($this->getReference($customerReference));
        $customerApplication->setApplication($this->getReference($application));
        $om->persist($customerApplication);        
    }

    public function createCustomer(ObjectManager $om, $name, $email, $customerReference)
    {
        $nav = new \CanalTP\NmmPortalBundle\Entity\NavitiaEntity();
        $nav->setEmail($email);
        $nav->setName($name);
        $om->persist($nav);

        $customer = new \CanalTP\NmmPortalBundle\Entity\Customer();
        $customer->setName($name);
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