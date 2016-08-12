<?php

namespace FunPro\FinancialBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\FinancialBundle\Entity\Currency;

/**
 * Class LoadCurrencyData
 *
 * @package FunPro\FinancialBundle\DataFixtures\ORM
 */
class LoadCurrencyData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $irrCurrency = (new Currency())
            ->setCode('IRR')
            ->setName('ریال');

        $usdCurrency = (new Currency())
            ->setCode('USD')
            ->setName('United State Dollar');

        $this->setReference('irrCurrency', $irrCurrency);
        $this->setReference('usdCurrency', $usdCurrency);

        $manager->persist($irrCurrency);
        $manager->persist($usdCurrency);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 10;
    }
}
