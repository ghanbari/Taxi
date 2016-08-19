<?php

namespace FunPro\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\ServiceBundle\Entity\Service;
use FunPro\UserBundle\Entity\Message;
use FunPro\UserBundle\Entity\User;

/**
 * MessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageRepository extends EntityRepository
{
    public function getRequestMessageToDriver(Driver $driver, Service $service)
    {
        $qb = $this->createQueryBuilder('m');

        return $qb->select('m')
            ->innerJoin('m.device', 'd')
            ->where($qb->expr()->eq('m.service', ':service'))
            ->andWhere($qb->expr()->eq('m.type', Message::MESSAGE_TYPE_SERVICE_REQUESTED))
            ->andWhere($qb->expr()->eq('d.owner', ':driver'))
            ->setParameter('service', $service)
            ->setParameter('driver', $driver)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getAllNonDownloaded(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('m');

        return $queryBuilder
            ->select(array('m', 'd'))
            ->innerJoin('m.device', 'd')
            ->where($queryBuilder->expr()->eq('d.owner', ':owner'))
            ->andWhere($queryBuilder->expr()->eq('m.download', ':false'))
            ->andWhere($queryBuilder->expr()->gte('m.createdAt', ':time'))
            ->setParameter('owner', $user)
            ->setParameter('false', false)
            ->setParameter('time', new \DateTime('-2 hour'))
            ->getQuery()
            ->setMaxResults(100)
            ->getResult();
    }
}
