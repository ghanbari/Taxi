<?php

namespace FunPro\DriverBundle\Entity;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * CarRoute
 *
 * @ORM\Table(name="car_route")
 * @ORM\Entity(repositoryClass="FunPro\DriverBundle\Repository\CarRouteRepository")
 */
class CarRoute
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var Car
     *
     * @ORM\ManyToOne(targetEntity="FunPro\DriverBundle\Entity\Car")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $car;

    /**
     * @var LineString
     *
     * @ORM\Column(type="linestring")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $route;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="update_at", type="datetime", nullable=true)
     * @Gedmo\Timestampable(on="update")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $updateAt;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_finished", type="boolean")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $finished;

    public function __construct($car)
    {
        $this->car = $car;
        $this->setFinished(false);
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set route
     *
     * @param LineString $route
     * @return CarRoute
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Get route
     *
     * @return Linestring
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get updateAt
     *
     * @return \DateTime
     */
    public function getUpdateAt()
    {
        return $this->updateAt;
    }

    /**
     * Set car
     *
     * @param Car $car
     * @return CarRoute
     */
    public function setCar(Car $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get car
     *
     * @return Car
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * @return boolean
     */
    public function isFinished()
    {
        return $this->finished;
    }

    /**
     * @param boolean $finished
     *
     * @return $this
     */
    public function setFinished($finished)
    {
        $this->finished = $finished;
        return $this;
    }
}
