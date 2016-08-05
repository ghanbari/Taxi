<?php

namespace FunPro\GeoBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JS;

/**
 * IpLocation
 *
 * @ORM\Table(name="ip_location")
 * @ORM\Entity(repositoryClass="FunPro\GeoBundle\Repository\IpLocationRepository")
 */
class IpLocation
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
     * @var Point
     *
     * @ORM\Column(name="location", type="point")
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $location;

    /**
     * @var string
     *
     * @ORM\Column(name="real_ip", length=50)
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(max="50", groups={"Create"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $realIp;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", length=50)
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Length(max="50", groups={"Create"})
     * @Assert\Ip(groups={"Create"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $ip;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="reporter", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Assert\NotBlank(groups={"Create"})
     * @Assert\Type(type="FunPro\UserBundle\Entity\User", groups={"Create"})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $reporter;

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
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
        return $this;
    }

    /**
     * @return Point
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Point $location
     *
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return string
     */
    public function getRealIp()
    {
        return $this->realIp;
    }

    /**
     * @param string $realIp
     *
     * @return $this
     */
    public function setRealIp($realIp)
    {
        $this->realIp = $realIp;
        return $this;
    }

    /**
     * @return User
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @param User $reporter
     *
     * @return $this
     */
    public function setReporter($reporter)
    {
        $this->reporter = $reporter;
        return $this;
    }
}
