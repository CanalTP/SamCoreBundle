<?php

namespace CanalTP\SamCoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use CanalTP\SamCoreBundle\Entity\CustomerApplication;
use CanalTP\SamCoreBundle\Entity\Customer;
use CanalTP\SamCoreBundle\Entity\Application;
use CanalTP\NmmPortalBundle\Services\NavitiaTokenManager;

class ApplicationToCustomerApplicationTransformer implements DataTransformerInterface
{
    private $om;
    private $navitiaTokenManager;
    private $oldCustomerApplications;
    private $oldPerimetersCode;

    public function __construct(ObjectManager $om, NavitiaTokenManager $navitiaTokenManager)
    {
        $this->om = $om;
        $this->navitiaTokenManager = $navitiaTokenManager;
        $this->oldCustomerApplications = array();
        $this->oldPerimeters = null;
    }

    private function generatePerimertersArray($perimeters)
    {
        $result = array();

        foreach ($perimeters as $perimeter) {
            $result[$perimeter->getExternalCoverageId()] = $perimeter->getExternalCoverageId();
        }
        return ($result);
    }

    public function transform($customer)
    {
        if ($customer === null) {
            return ($customer);
        }
        $applications = new ArrayCollection();
        $this->oldPerimeters = $this->generatePerimertersArray($customer->getNavitiaEntity()->getPerimeters());

        foreach ($customer->getActiveCustomerApplications() as $customerApplication) {
            $customerApplication->setIsActive(false);
            $applications->add($customerApplication->getApplication());
            $this->oldCustomerApplications[$customerApplication->getApplication()->getId()] = $customerApplication;
        }
        $customer->setApplications($applications);
        return $customer;
    }

    private function createCustomerApplicationRelation($customer, Application $application)
    {
        $customerApplication = new CustomerApplication();

        $customerApplication->setCustomer($customer);
        $customerApplication->setApplication($application);
        $customerApplication->setToken(
            $this->navitiaTokenManager->generateToken()
        );
        $customerApplication->setIsActive(true);
        return ($customerApplication);
    }

    private function clearUnusableTokens()
    {
        foreach ($this->oldCustomerApplications as $oldCustomerApplication) {
            $this->navitiaTokenManager->deleteToken($oldCustomerApplication->getToken());
        }
    }

    public function reverseTransform($customer)
    {
        if (!$customer) {
            return $customer;
        }
        $customerApplications = new ArrayCollection();
        $this->navitiaTokenManager->initUser($customer->getNameCanonical(), $customer->getEmailCanonical());
        $this->navitiaTokenManager->initInstanceAndAuthorizations($customer->getNavitiaEntity()->getPerimeters());
        $forceGenerateToken = !($this->oldPerimeters == $this->generatePerimertersArray($customer->getNavitiaEntity()->getPerimeters()));

        foreach ($customer->getApplications() as $application) {
            if (!$forceGenerateToken && array_key_exists($application->getId(), $this->oldCustomerApplications)) {
                $customerApplication = $this->oldCustomerApplications[$application->getId()];
                $customerApplication->setIsActive(true);
                $customerApplications->add($customerApplication);
                unset($this->oldCustomerApplications[$application->getId()]);
            } else {
                $customerApplications->add(
                    $this->createCustomerApplicationRelation(
                        $customer,
                        $application
                    )
                );
            }
        }
        $this->clearUnusableTokens();
        $customer->setApplications($customerApplications);
        return $customer;
    }
}
