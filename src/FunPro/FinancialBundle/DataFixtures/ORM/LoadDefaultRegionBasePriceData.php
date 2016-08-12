<?php

namespace FunPro\FinancialBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\FinancialBundle\Entity\RegionBasePrice;

/**
 * Class LoadDefaultRegionBasePriceData
 *
 * @package FunPro\FinancialBundle\DataFixtures\ORM
 */
class LoadDefaultRegionBasePriceData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $defaultRegion = $this->getReference('defaultRegion');

        foreach ($defaultRegion->getCurrencies() as $currency) {
            $manager->persist(new RegionBasePrice($defaultRegion, $currency, 10));
        }

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 12;
    }
}
