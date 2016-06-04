<?php

namespace FunPro\EngineBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\DriverBundle\Entity\Car;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\DriverBundle\Entity\Plaque;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\GeoBundle\Entity\Address;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
        $i = 1;

        foreach ($credentials as $nationalCode => $pass) {
            $car = new Car();
            $car->setBorn(new \DateTime());
            $car->setColor('red');
            $car->setType('405');
            $car->setBrand('Iran Khodro');
            $car->setDriver($this->getReference('driver-' . $i));
            $car->setCurrent(true);

            $plaque = (new Plaque())
                ->setFirstNumber(22)
                ->setAreaCode("‌ب")
                ->setSecondNumber(rand(1, 999))
                ->setCityNumber(rand(10, 99));
            $car->setPlaque($plaque);

            $manager->persist($car);
            $this->setReference('car-' . $i, $car);
            $i++;
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