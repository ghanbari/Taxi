<?php

namespace FunPro\ServiceBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\ServiceBundle\Entity\Wakeful;

/**
 * WakefulRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WakefulRepository extends EntityRepository
{
    /**
     * @param        $longitude
     * @param        $latitude
     * @param int    $distance in kilometer
     * @param int    $limit
     * @param Driver $disappear
     *
     * @return Wakeful[]
     */
    public function getAllFreeAndNearWakeful(
        $longitude,
        $latitude,
        $distance = 2000,
        $limit = 500,
        Driver $disappear = null
    )
    {
        $queryBuilder = $this->getAllNearWakefulQueryBuilder($longitude, $latitude, $distance, $disappear);

        $wakefuls = $queryBuilder
            ->andWhere($queryBuilder->expr()->in('c.status', ':status'))
            ->setParameter('status', array(Car::STATUS_WAKEFUL, Car::STATUS_SERVICE_END))
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();

        return $wakefuls;
    }

    /**
     * @param        $longitude
     * @param        $latitude
     * @param int    $distance in kilometer
     * @param Driver $disappear
     *
     * @return QueryBuilder
     */
    public function getAllNearWakefulQueryBuilder(
        $longitude,
        $latitude,
        $distance = 2000,
        Driver $disappear = null
    )
    {
        $queryBuilder = $this->createQueryBuilder('w');

        $queryBuilder->select(array('w', 'c', 'd'))
            ->join('w.car', 'c')
            ->join('c.driver', 'd')
            ->where($queryBuilder->expr()->lte('glength(linestring(w.point, st_geomfromtext(:point)))', ':distance'))
            ->setParameter('point', "Point($longitude $latitude)")
            ->setParameter('distance', $distance / 110000);

        if (!is_null($disappear)) {
            $queryBuilder->andWhere($queryBuilder->expr()->neq('d.id', ':disappear'))
                ->setParameter('disappear', $disappear->getId());
        }

        return $queryBuilder;
    }

    /**
     * @param        $longitude
     * @param        $latitude
     * @param int    $distance in kilometer
     * @param int    $limit
     * @param Driver $disappear
     *
     * @return Wakeful[]
     */
    public function getAllNearWakeful(
        $longitude,
        $latitude,
        $distance = 2000,
        $limit = 500,
        Driver $disappear = null
    )
    {
        $queryBuilder = $this->getAllNearWakefulQueryBuilder($longitude, $latitude, $distance, $disappear);

        $wakefuls = $queryBuilder
            ->getQuery()
            ->setMaxResults($limit)
            ->getResult();

        return $wakefuls;
    }
}
