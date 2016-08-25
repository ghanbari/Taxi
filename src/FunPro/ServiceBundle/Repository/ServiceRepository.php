<?php

namespace FunPro\ServiceBundle\Repository;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\EntityRepository;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Entity\FloatingCost;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\ServiceBundle\Entity\ServiceLog;

/**
 * ServiceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ServiceRepository extends EntityRepository
{
    /**
     * Get a service [hydrate: car, plaque, driver] by id
     *
     * @param $serviceId
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Service
     */
    public function getWithCar($serviceId)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder->select(array('c', 's', 'p', 'd'))
            ->innerJoin('s.car', 'c')
            ->innerJoin('c.plaque', 'p')
            ->innerJoin('c.driver', 'd')
            ->where($queryBuilder->expr()->eq('s.id', ':service_id'))
            ->setParameter('service_id', $serviceId)
            ->getQuery()
            ->getSingleResult();
    }

    /**
     * @param Driver $driver
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastServiceOfDriver(Driver $driver)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder->select(array('s', 'p', 'a', 'car'))
            ->innerJoin('s.logs', 'l')
            ->innerJoin('s.car', 'car')
            ->leftJoin('s.passenger', 'p')
            ->leftJoin('s.agent', 'a')
            ->where($queryBuilder->expr()->eq('car.driver', ':driver'))
            ->orderBy('l.atTime', 'DESC')
            ->setParameter('driver', $driver)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * @param Passenger $passenger
     *
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getLastServiceOfPassenger(Passenger $passenger)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder->select(array('s', 'p', 'car', 'c'))
            ->innerJoin('s.logs', 'l')
            ->innerJoin('s.passenger', 'p')
            ->leftJoin('s.car', 'car')
            ->leftJoin('s.canceledReason', 'c')
            ->where($queryBuilder->expr()->eq('s.passenger', ':passenger'))
            ->andWhere($queryBuilder->expr()->neq('s.status', ':status'))
            ->orderBy('l.atTime', 'DESC')
            ->setParameter('passenger', $passenger)
            ->setParameter('status', ServiceLog::STATUS_REQUESTED)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    /**
     * Calculate total cost of service
     *
     * @param Service $service
     *
     * @return int
     */
    public function getTotalCost(Service $service)
    {
        $cost = $service->getPrice();
        /** @var FloatingCost $floatCost */
        foreach ($service->getFloatingCosts() as $floatCost) {
            $cost += $floatCost->getCost();
        }

        return $cost;
    }

    /**
     * Get a service[hydrate: logs] that is doing by given car now.
     *
     * @param Car $car
     *
     * @return Service
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getDoingServiceFilterByCar(Car $car)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        $service = $queryBuilder->select(array('s'))
            ->innerJoin('s.logs', 'l')
            ->where($queryBuilder->expr()->eq('s.car', ':car'))
            ->andWhere($queryBuilder->expr()->eq('s.status', ':status'))
            ->setParameter('car', $car)
            ->setParameter('status', ServiceLog::STATUS_START)
            ->orderBy('l.atTime', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if ($service and $service->getLogs()->last()->getStatus() == ServiceLog::STATUS_START) {
            return $service;
        }
    }

    /**
     * @param Point     $origin
     * @param Point     $destination
     * @param \DateTime $from
     * @param \DateTime $till
     * @param int       $limit
     * @param int       $offset
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilterByQueryBuilder(
        Point $origin = null,
        Point $destination = null,
        \DateTime $from = null,
        \DateTime $till = null,
        $limit = 10,
        $offset = 0
    ) {
        $queryBuilder = $this->createQueryBuilder('s');

        if ($origin) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte('Distance(s.startPoint, geomfromtext(:origin))', 0.01))
                ->setParameter('origin', $origin);
        }

        if ($destination) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte('Distance(s.endPoint, geomfromtext(:destination))', 0.01))
                ->setParameter('destination', $destination);
        }

        if ($from) {
            $queryBuilder->andWhere($queryBuilder->expr()->gte('s.createdAt', ':from'))
                ->setParameter('from', $from);
        }

        if ($till) {
            $queryBuilder->andWhere($queryBuilder->expr()->lte('s.createdAt', ':till'))
                ->setParameter('till', $till);
        }

        return $queryBuilder
            ->setMaxResults($limit)
            ->setFirstResult($offset);
    }

    /**
     * @param Passenger $passenger
     * @param Driver    $driver
     * @param Point     $origin
     * @param Point     $destination
     * @param \DateTime $from
     * @param \DateTime $till
     * @param int       $limit
     * @param int       $offset
     *
     * @return array
     */
    public function getPassengerServiceFilterBy(
        Passenger $passenger,
        Driver $driver = null,
        Point $origin = null,
        Point $destination = null,
        \DateTime $from = null,
        \DateTime $till = null,
        $limit = 10,
        $offset = 0
    ) {
        $queryBuilder = $this->getFilterByQueryBuilder($origin, $destination, $from, $till, $limit, $offset);

        $queryBuilder->select(array('s', 'p', 'c', 'd', 'l'));
        $queryBuilder
            ->innerJoin('s.passenger', 'p')
            ->innerJoin('s.car', 'c')
            ->innerJoin('c.driver', 'd')
            ->innerJoin('s.logs', 'l');

        $queryBuilder->andWhere($queryBuilder->expr()->eq('s.passenger', ':passenger'))
            ->setParameter('passenger', $passenger);

        if ($driver) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('c.driver', ':driver'))
                ->setParameter('driver', $driver);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function getDriverServiceFilterBy(
        Driver $driver,
        Point $origin = null,
        Point $destination = null,
        \DateTime $from = null,
        \DateTime $till = null,
        $limit = 10,
        $offset = 0
    ) {
        $queryBuilder = $this->getFilterByQueryBuilder($origin, $destination, $from, $till, $limit, $offset);

        $queryBuilder->select(array('s', 'p', 'l'));
        $queryBuilder
            ->innerJoin('s.passenger', 'p')
            ->innerJoin('s.car', 'c')
            ->innerJoin('c.driver', 'd')
            ->innerJoin('s.logs', 'l');

        $queryBuilder->andWhere($queryBuilder->expr()->eq('c.driver', ':driver'))
            ->setParameter('driver', $driver);

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
