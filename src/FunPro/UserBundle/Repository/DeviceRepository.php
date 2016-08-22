<?php

namespace FunPro\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FunPro\UserBundle\Entity\User;

/**
 * Class DeviceRepository
 *
 * @package FunPro\UserBundle\Repository
 */
class DeviceRepository extends EntityRepository
{
    public function removeUserDevices(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('d');

        $queryBuilder->delete()
            ->where($queryBuilder->expr()->eq('d.owner', ':owner'))
            ->setParameter('owner', $user)
            ->getQuery()
            ->setHint(
                \Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER,
                'Gedmo\SoftDeleteable\Query\TreeWalker\SoftDeleteableWalker'
            )->execute();
    }
}
