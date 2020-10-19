<?php

namespace CanalTP\SamCoreBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\SamEcoreApplicationManagerBundle\Services\ApplicationFinder;
use CanalTP\SamCoreBundle\Entity\CustomerApplication;
use CanalTP\SamCoreBundle\Entity\Application;
use CanalTP\SamCoreBundle\Entity\Customer;

class CustomerManager
{
    protected $om = null;
    protected $repository = null;
    protected $navitiaTokenManager = null;
    private $applicationFinder = null;

    public function __construct(
        ObjectManager $om,
        $navitiaTokenManager,
        ApplicationFinder $applicationFinder
    ) {
        $this->om = $om;
        $this->repository = $this->om->getRepository('CanalTPSamCoreBundle:Customer');
        $this->navitiaTokenManager = $navitiaTokenManager;
        $this->applicationFinder = $applicationFinder;
    }

    public function findAll()
    {
        return ($this->repository->findAll());
    }

    public function find($customerId)
    {
        return empty($customerId) ? null : $this->repository->find($customerId);
    }

    protected function syncPerimeters($customer)
    {
        $perimeterRepo = $this->om->getRepository('CanalTPSamCoreBundle:Perimeter');
        $perimeters = $perimeterRepo->findBy(array('customer' => $customer));
        $officialPerimeterIds = array();

        foreach ($customer->getPerimeters() as $perimeter) {
            if ($perimeter->getId() != null) {
                $officialPerimeterIds[] = $perimeter->getId();
            }
        }

        foreach ($perimeters as $perimeter) {
            if (!in_array($perimeter->getId(), $officialPerimeterIds)) {
                $this->om->remove($perimeter);
            }
        }
    }

    public function save($customer)
    {
        if ($customer->getId() != null) {
            $this->syncPerimeters($customer);
        }
        $customer->refreshPerimeters();
        // TODO: UniqueEntity not work in perimeter entity.
        $customer->setPerimeters(array_unique($customer->getPerimeters()->toArray()));
        $this->om->persist($customer);
        $customer->upload();
        $this->om->flush();
    }

    public function findAllToArray()
    {
        return ($this->repository->findAllToArray());
    }

    public function disableTokens($customer, Application $application = null)
    {
        $this->repository->disableTokens($customer, $application);
    }

    public function getApplications($customer)
    {
        $applications = array();
        foreach ($customer->getActiveCustomerApplications() as $customerApplication) {
            $application = $customerApplication->getApplication();

            $applications[$application->getCanonicalName()] = $application;
        }

        return $applications;
    }

    public function generateTokens($customer, $applications)
    {
        foreach ($applications as $application) {
            $this->createCustomerApplicationRelation($customer, $application);
        }

        return true;
    }

    public function generateToken($customer, $application)
    {
        $this->createCustomerApplicationRelation($customer, $application);
    }


    public function initTokenManager($name, $email, $perimeters)
    {
        $this->navitiaTokenManager->initUser($name, $email);
        $this->navitiaTokenManager->initInstanceAndAuthorizations($perimeters);
    }

    protected function createCustomerApplicationRelation($customer, Application $application)
    {
        $customerApplication = new CustomerApplication();

        $customerApplication->setCustomer($customer);
        $customerApplication->setApplication($application);
        $customerApplication->setToken(
            $this->navitiaTokenManager->generateToken()
        );
        $customerApplication->setIsActive(true);

        $this->om->persist($customerApplication);
        $this->om->flush($customerApplication);

        return $customerApplication;
    }

    public function findByCurrentApp()
    {
        $customerApplicationRepository = $this->om->getRepository('CanalTPSamCoreBundle:CustomerApplication');

        return ($customerApplicationRepository->findBy(
            array(
                'application' => $this->applicationFinder->getCurrentApp()->getId(),
                'isActive' => true
            )
        ));
    }

    public function findByCurrentApplication()
    {
        $customerRepository = $this->om->getRepository('CanalTPSamCoreBundle:Customer');

        return $customerRepository->findByActiveApplication(
            $this->applicationFinder->getCurrentApp()->getId()
        );
    }
}
