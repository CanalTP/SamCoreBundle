<?php

namespace CanalTP\SamCoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * CustomerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CustomerRepository extends EntityRepository
{
    private $filter = array('locked' => false);

    public function findAll() {
        return parent::findBy($this->filter);
    }

    public function findBy(
        array $criterias = [],
        array $orderBy = null,
        $limit = null,
        $offset = null)
    {
        $criterias = array_merge($criterias, $this->filter);
        return parent::findBy($criterias, $orderBy, $limit, $offset);
    }

    public function findAllToArray()
    {
        $customers = array();

        foreach ($this->findAll() as $customer) {
            $customers[$customer->getId()] = $customer->getName();
        }
        return ($customers);
    }

    public function findByToArray(array $criterias = [])
    {
        $customers = array();

        foreach ($this->findBy($criterias) as $customer) {
            $customers[$customer->getId()] = $customer->getName();
        }
        return ($customers);
    }

    public function disableTokens($customer, Application $application = null)
    {
        $queryText = 'UPDATE CanalTPSamCoreBundle:CustomerApplication c '
                . 'SET c.isActive = false '
                . 'WHERE c.customer=:customer';


        if (!is_null($application)) {
            $queryText .= ' AND c.application=:application';
        }

        $query = $this->_em->createQuery($queryText);
        $query->setParameter('customer', $customer);

        if (!is_null($application)) {
            $query->setParameter('application', $application);
        }

        $query->execute();
    }

    public function findByActiveApplication($applicationId)
    {
        $qb = $this->createQueryBuilder('c')
            ->distinct()
            ->join('c.applications', 'ca')
            ->where('ca.application = :appId')
            ->andWhere('ca.isActive = true')
            ->andWhere('c.locked = false')
            ->setParameter('appId', $applicationId);

        return $qb->getQuery()->getResult();
    }
}
