<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\PassengerBundle\Entity\Passenger;

/**
 * DiscountWrongCount
 *
 * @ORM\Table(name="discount_wrong_count")
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\DiscountWrongCountRepository")
 */
class DiscountWrongCount
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Passenger
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     */
    private $user;

    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="created_at",type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="code")
     */
    private $code;

    /**
     * DiscountWrongCount constructor.
     * @param Passenger $user
     * @param string $code
     */
    public function __construct(Passenger $user, $code)
    {
        $this->user = $user;
        $this->createdAt = new \DateTime();
        $this->code = $code;
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
     * @return Passenger
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Passenger $user
     * @return DiscountWrongCount;
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return DiscountWrongCount;
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return DiscountWrongCount;
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }
}
