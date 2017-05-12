<?php

namespace FunPro\FinancialBundle\DataFixtures\ORM;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use FunPro\FinancialBundle\Entity\Region;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;

class LoadDefaultRegionData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $polygon = new Polygon(array(
            new LineString(array(
                new Point(-180, -90),
                new Point(180, -90),
                new Point(180, 90),
                new Point(-180, 90),
                new Point(-180, -90),
            ))
        ));

        $region = (new Region())
            ->setRegion($polygon)
            ->addCurrency($this->getReference('irrCurrency'));

        $this->setReference('defaultRegion', $region);

        $manager->persist($region);
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
