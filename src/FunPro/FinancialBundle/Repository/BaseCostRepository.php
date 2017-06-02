<?php

namespace FunPro\FinancialBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * BaseCostRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BaseCostRepository extends EntityRepository
{
    /**
     * @param $longitude
     * @param $latitude
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLast($longitude, $latitude)
    {
        $qb = $this->createQueryBuilder('bc');

        return $qb->select('bc')
            ->where($qb->expr()->lte('glength(linestring(bc.location, st_geomfromtext(:point))) * 110000', 'bc.locationRadius'))
            ->setParameter('point', "Point($longitude $latitude)")
            ->setMaxResults(1)
            ->orderBy('bc.createdAt', 'DESC')
            ->getQuery()
            ->getSingleResult();
    }
}
