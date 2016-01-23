<?php

namespace FunPro\EngineBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\DriverBundle\Entity\Driver;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CreateDriverForTest extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $agencies = array_keys($this->container->getParameter('test.agency.admin.credentials'));
        $i = 1;

        foreach ($credentials as $nationalCode => $pass) {
            $driver = new Driver();
            $driver->setEmail($nationalCode.'@itaxico.ir');
            $driver->setUsername($nationalCode);
            $driver->setPlainPassword($pass);
            $driver->setNationalCode($nationalCode);
            $driver->setName('driver-'.$i);
            $driver->setEnabled(true);
            $driver->setContractNumber($i);
            $driver->addRole('ROLE_DRIVER');
            $driver->setAgency($this->getReference('agency-'. $agencies[rand(0, count($agencies)-1)]));
            $manager->persist($driver);
            $this->setReference('driver-'.$i, $driver);
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
        return 22;
    }
} 