<?php

namespace FunPro\FinancialBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\FinancialBundle\Entity\BaseCost;

/**
 * Class LoadBaseCostData
 *
 * @package FunPro\FinancialBundle\DataFixtures\ORM
 */
class LoadBaseCostData implements FixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $cost = new BaseCost();
        $cost
            ->setCostPerMeter(1)
            ->setDiscountPercent(5)
            ->setEntranceFee(500);

        $manager->persist($cost);
        $manager->flush();
    }
}
