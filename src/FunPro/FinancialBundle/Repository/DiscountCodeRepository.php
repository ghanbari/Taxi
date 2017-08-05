<?php

namespace FunPro\FinancialBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FunPro\FinancialBundle\Entity\DiscountCode;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\GeoBundle\Utility\Util;
use FunPro\PassengerBundle\Entity\Passenger;
use FunPro\ServiceBundle\Entity\Service;

/**
 * DiscountCodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class DiscountCodeRepository extends EntityRepository
{
    /**
     * @param Passenger $passenger
     * @param DiscountCode $discountCode
     * @param Point $origin
     * @param Point $destination
     * @return bool
     */
    public function canUseDiscount(Passenger $passenger, DiscountCode $discountCode, Point $origin=null, Point $destination=null)
    {
        $distance = Util::distance(
            $origin->getLatitude(),
            $origin->getLongitude(),
            $discountCode->getOriginLocation()->getLatitude(),
            $discountCode->getOriginLocation()->getLongitude()
        );

        if ($distance > $discountCode->getLocationRadius()) {
            return false;
        }

        $usageCountPerUser = $this->getUsageCount($discountCode, $passenger);
        if ($usageCountPerUser and $usageCountPerUser >= $discountCode->getMaxUsagePerUser()) {
            return false;
        }

        $usageCount = $this->getUsageCount($discountCode);
        if ($usageCount and $usageCount >= $discountCode->getMaxUsage()) {
            return false;
        }
        $expireDate = $discountCode->getExpiredAt();
        $expireDate->setTime(23, 59, 59);
        
        if ($expireDate->getTimestamp() < time()) {
            return false;
        }

        return true;
    }

    public function getUsageCount(DiscountCode $discountCode, Passenger $passenger = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('count(s)')
            ->from('FunProServiceBundle:Service', 's');

        $qb->where($qb->expr()->eq('s.discountCode', ':discountCode'))
            ->setParameter('discountCode', $discountCode);

        if ($passenger) {
            $qb->andWhere($qb->expr()->eq('s.passenger', ':passenger'))
                ->setParameter('passenger', $passenger);
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }
}
