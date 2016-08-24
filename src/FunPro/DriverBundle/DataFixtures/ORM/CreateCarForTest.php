<?php

namespace FunPro\EngineBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Plaque;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CreateCarForTest
 *
 * @package FunPro\EngineBundle\DataFixtures\ORM
 */
class CreateCarForTest extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * @var Container
     */
    private $container;

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        if (!$this->container->getParameter('test.driver')) {
            return;
        }

        $credentials = $this->container->getParameter('test.driver.credentials');

        $countOfDriver = count($credentials);
        for ($counter = 1; $counter <= $countOfDriver; $counter++) {
            $car = new Car();
            $car->setBorn(new \DateTime());
            $car->setColor('red');
            $car->setType('405');
            $car->setBrand('Iran Khodro');
            $car->setDriver($this->getReference('driver-' . $counter));
            $car->setCurrent(true);

            $plaque = (new Plaque())
                ->setFirstNumber(22)
                ->setAreaCode('‌ب')
                ->setSecondNumber(mt_rand(1, 999))
                ->setCityNumber(mt_rand(10, 99));
            $car->setPlaque($plaque);

            $manager->persist($car);
            $this->setReference('car-' . $counter, $car);
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
        return 23;
    }
}
