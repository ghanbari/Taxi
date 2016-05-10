<?php

namespace FunPro\ServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\DriverBundle\Entity\Car;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use Symfony\Component\Validator\Constraints\DateTime;
use JMS\Serializer\Annotation as JS;

/**
 * Wakeful
 *
 * @ORM\Table(
 *      name="wakeful",
 *      indexes={@ORM\Index(name="wakeful_spatial_point", columns={"point"})}
 * )
 * @ORM\Entity(repositoryClass="FunPro\ServiceBundle\Repository\WakefulRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Wakeful
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var Car
     *
     * @ORM\OneToOne(targetEntity="FunPro\DriverBundle\Entity\Car", inversedBy="wakeful")
     * @ORM\JoinColumn(name="car_id", referencedColumnName="id", onDelete="cascade")
     *
     * @JS\Groups({"Car"})
     * @JS\MaxDepth(2)
     * @JS\Since("1.0.0")
     */
    private $car;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="at_time", type="datetime")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $atTime;

    /**
     * @var Point
     *
     * @ORM\Column(name="point", type="point")
     *
     * @JS\Groups({"Point"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $point;

    public function __construct(Car $car, Point $point)
    {
        $this->setCar($car);
        $this->setPoint($point);
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
     * Set atTime
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function setAtTime()
    {
        $this->atTime = new \DateTime();
    }

    /**
     * Get atTime
     *
     * @return \DateTime 
     */
    public function getAtTime()
    {
        return $this->atTime;
    }

    /**
     * Set point
     *
     * @param point $point
     * @return Wakeful
     */
    public function setPoint($point)
    {
        $this->point = $point;
        $this->setAtTime(new \DateTime());

        return $this;
    }

    /**
     * Get point
     *
     * @return point 
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * Set car
     *
     * @param \FunPro\DriverBundle\Entity\Car $car
     * @return Wakeful
     */
    public function setCar(\FunPro\DriverBundle\Entity\Car $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get car
     *
     * @return \FunPro\DriverBundle\Entity\Car 
     */
    public function getCar()
    {
        return $this->car;
    }
}
