<?php

namespace FunPro\AgentBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\AgentBundle\Entity\Agency;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\GeoBundle\Entity\Address;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\VarDumper\VarDumper;

class CreateAgencyForTest extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        if (!$this->container->getParameter('test.passenger')) {
            return;
        }

        $credentials = $this->container->getParameter('test.agency.admin.credentials');
        $city = $manager->getRepository('FunProGeoBundle:City')->find(1);

        foreach ($credentials as $username => $pass) {
            $user = $this->getReference('agency-admin-' . $username);

            $address = new Address();
            $address->setTitle('test');
            $address->setAddress('test');
            $address->setCity($city);
            $address->setPoint(new Point(32.869435, 59.220989));
            $manager->persist($address);

            $agency = new Agency();
            $agency->setName('test');
            $agency->setAdmin($user);
            $agency->setAddress($address);

            $manager->persist($agency);

            $this->setReference('agency-' . $username, $agency);
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
        return 21;
    }
} 