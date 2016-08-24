<?php

namespace FunPro\EngineBundle\DataFixtures\ORM;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\DriverBundle\Entity\Driver;
use FunPro\GeoBundle\Entity\Address;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CreateDriverForTest
 *
 * @package FunPro\EngineBundle\DataFixtures\ORM
 */
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
        $city = $manager->getRepository('FunProGeoBundle:City')->find(1);
        $agencies = array_keys($this->container->getParameter('test.agency.admin.credentials'));
        $host = $this->container->getParameter('router.request_context.host');
        $counter = 1;

        foreach ($credentials as $nationalCode => $pass) {
            do {
                $apiKey = bin2hex(random_bytes(100));
                $isDuplicate = $manager->getRepository('FunProUserBundle:Device')
                    ->findOneByApiKey($apiKey);
            } while ($isDuplicate);

            $driver = new Driver();
            $driver->setEmail($nationalCode.'@'.$host);
            $driver->setUsername($nationalCode);
            $driver->setPlainPassword($pass);
            $driver->setNationalCode($nationalCode);
            $driver->setName('driver-'.$counter);
            $driver->setEnabled(true);
            $driver->setApiKey($apiKey);
            $driver->setContractNumber($counter);
            $driver->setMobile('09' . substr($nationalCode, 1));
            $driver->setAgency($this->getReference('agency-'. $agencies[mt_rand(0, count($agencies)-1)]));

            $address = new Address();
            $address->setTitle('test');
            $address->setAddress('test');
            $address->setCity($city);
            $address->setPoint(new Point(32.869435, 59.220989));
            $driver->setAddress($address);

            $manager->persist($driver);
            $this->setReference('driver-'.$counter, $driver);
            $counter++;
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
