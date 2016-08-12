<?php

namespace FunPro\FinancialBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\FinancialBundle\Entity\CurrencyExchangeLog;

/**
 * Class LoadCurrencyLog
 *
 * @package FunPro\FinancialBundle\DataFixtures\ORM
 */
class LoadCurrencyLog extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $irrCurrencyLog = (new CurrencyExchangeLog())
            ->setCurrency($this->getReference('irrCurrency'))
            ->setExchange(1);

        $usdCurrencyLog = (new CurrencyExchangeLog())
            ->setCurrency($this->getReference('usdCurrency'))
            ->setExchange(1);

        $this->setReference('irrCurrencyLog', $irrCurrencyLog);
        $this->setReference('usdCurrencyLog', $usdCurrencyLog);

        $manager->persist($irrCurrencyLog);
        $manager->persist($usdCurrencyLog);

        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 11;
    }
}
