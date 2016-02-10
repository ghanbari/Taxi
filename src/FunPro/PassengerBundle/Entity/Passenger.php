<?php

namespace FunPro\PassengerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JS;

/**
 * Passenger
 *
 * @ORM\Table(name="passenger")
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\PassengerRepository")
 */
class Passenger extends User
{
    /**
     * @var string
     *
     * @ORM\Column(name="mobile", type="string", length=11, nullable=true)
     *
     * @Assert\NotBlank(groups={"Register", "Profile"})
     * @Assert\Regex(pattern="/09\d{9}/", groups={"Register", "Profile"})
     *
     * @JS\Groups({"PassengerMobile", "Profile"})
     * @JS\Since("1.0.0")
     */
    protected $mobile;

    /**
     * @var string
     *
     * @ORM\Column(name="mobile_canonical", type="string", length=11, unique=true, nullable=true)
     *
     * @JS\Groups({"PassengerMobile", "Profile"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("validatedNumber")
     */
    protected $mobileCanonical;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\PassengerBundle\Entity\Passenger")
     * @ORM\JoinColumn(name="referer_id", referencedColumnName="id", onDelete="Restrict")
     *
     * @Assert\Type(type="FunPro\PassengerBundle\Entity\Passenger", groups={"Register", "Profile"})
     *
     * @JS\Groups({"Referrer", "Profile"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $referrer;

    /**
     * @var float
     *
     * @ORM\Column(name="rate", type="decimal", precision=2, scale=1, options={"default"=0})
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    private $rate;

    public function __construct()
    {
        parent::__construct();
        $this->addRole(self::ROLE_PASSENGER);
        $this->rate = 0;
    }

    /**
     * Set mobile
     *
     * @param string $mobile
     * @return Passenger
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * Get mobile
     *
     * @return string 
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * Get mobile canonical
     *
     * @return string
     */
    public function getMobileCanonical()
    {
        return $this->mobileCanonical;
    }

    /**
     * Set Mobile canonical
     *
     * @param string $mobileCanonical
     * @return $this
     */
    public function setMobileCanonical($mobileCanonical)
    {
        $this->mobileCanonical = $mobileCanonical;
        $this->setUsername($mobileCanonical);

        return $this;
    }

    /**
     * Set rate
     *
     * @param string $rate
     * @return Passenger
     */
    public function setRate($rate)
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * Get rate
     *
     * @return string 
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * Set referrer
     *
     * @param \FunPro\PassengerBundle\Entity\Passenger $referrer
     * @return Passenger
     */
    public function setReferrer(\FunPro\PassengerBundle\Entity\Passenger $referrer = null)
    {
        $this->referrer = $referrer;

        return $this;
    }

    /**
     * Get referrer
     *
     * @return \FunPro\PassengerBundle\Entity\Passenger 
     */
    public function getReferrer()
    {
        return $this->referrer;
    }
}
