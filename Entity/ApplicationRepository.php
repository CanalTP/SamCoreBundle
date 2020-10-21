<?php

namespace CanalTP\SamCoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ApplicationRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ApplicationRepository extends EntityRepository
{
    /**
     * Retourne tous les applications en base de données
     * @param type $user
     * @return type
     */
    public function findAllOrderedByName()
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.name')
            ->getQuery()
            ->useResultCache(true, 3600 * 24, 'applications')
            ->getResult();
    }

    public function findByCanonicalName($name)
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('r')
            ->leftJoin('a.roles', 'r')
            ->where('a.canonicalName = :app')
            ->setParameter('app', $name);

        return $qb->getQuery()->getSingleResult();
    }

    public function findByUser($user)
    {
        $qb = $this->createQueryBuilder('a')
            ->join('a.roles', 'r')
            ->join('r.users', 'u')
            ->where('u = :user')
            ->setParameter('user', $user)
            ->orderBy('a.name', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findWithEditableRoles($appId)
    {
        $qb = $this->createQueryBuilder('a')
            ->addSelect('r')
            ->leftJoin('a.roles', 'r', \Doctrine\ORM\Query\Expr\Join::WITH, 'r.isEditable = true')
            ->where('a.id = :id')
            ->orderBy('r.id', 'ASC')
            ->setParameter('id', $appId);

        return $qb->getQuery()->getSingleResult();
    }
}
