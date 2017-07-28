<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JS;

/**
 * BaseCost
 *
 * @ORM\Table(name="base_cost")
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\BaseCostRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class BaseCost
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="entrance_fee", type="integer")
     *
     * @Assert\NotNull(groups={"Create"})
     * @Assert\Type(type="numeric", groups={"Create"})
     *
     * @JS\Groups({"Admin", "Public"})
     * @JS\Since("1.0.0")
     */
    private $entranceFee;

    /**
     * @var float
     *
     * @ORM\Column(name="cost_per_meter", type="decimal", scale=2, precision=5)
     *
     * @Assert\NotNull(groups={"Create"})
     *
     * @JS\Groups({"Admin", "Public"})
     * @JS\Since("1.0.0")
     */
    private $costPerMeter;

    /**
     * @var integer
     *
     * @ORM\Column(name="discount_percent", type="smallint")
     *
     * @Assert\NotNull(groups={"Create"})
     * @Assert\Type(type="numeric", groups={"Create"})
     *
     * @JS\Groups({"Admin", "Public"})
     * @JS\Since("1.0.0")
     */
    private $discountPercent;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_cash_reward", type="smallint")
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Type(type="numeric", groups={"Create"})
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $paymentCashReward;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_credit_reward", type="smallint")
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Type(type="numeric", groups={"Create"})
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $paymentCreditReward;

    /**
     * @var Point
     *
     * @ORM\Column(name="location", type="point")
     *
     * @Assert\NotNull(groups={"Create"})
     * @Assert\Type(type="FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create"})
     * @Assert\Valid()
     *
     * @JS\Groups({"Admin", "Point", "Public"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $location;

    /**
     * @var integer
     *
     * @ORM\Column(name="location_radius", type="integer")
     *
     * @Assert\NotNull(groups={"Create"})
     * @Assert\Type("numeric")
     * @Assert\Valid()
     *
     * @JS\Groups({"Admin", "Point", "Public"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $locationRadius;

    /**
     * @var \Datetime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     *
     * @JS\Groups({"Admin"})
     * @JS\Since("1.0.0")
     */
    private $deletedAt;

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
     * @return float
     */
    public function getCostPerMeter()
    {
        return $this->costPerMeter;
    }

    /**
     * @param float $costPerMeter
     *
     * @return $this
     */
    public function setCostPerMeter($costPerMeter)
    {
        $this->costPerMeter = $costPerMeter;
        return $this;
    }

    /**
     * @return int
     */
    public function getDiscountPercent()
    {
        return $this->discountPercent;
    }

    /**
     * @param int $discountPercent
     *
     * @return $this
     */
    public function setDiscountPercent($discountPercent)
    {
        $this->discountPercent = $discountPercent;
        return $this;
    }

    /**
     * @return int
     */
    public function getEntranceFee()
    {
        return $this->entranceFee;
    }

    /**
     * @param int $entranceFee
     *
     * @return $this
     */
    public function setEntranceFee($entranceFee)
    {
        $this->entranceFee = $entranceFee;
        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \Datetime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\Datetime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(\DateTime $deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentCashReward()
    {
        return $this->paymentCashReward;
    }

    /**
     * @param int $paymentCashReward
     *
     * @return $this
     */
    public function setPaymentCashReward($paymentCashReward)
    {
        $this->paymentCashReward = $paymentCashReward;
        return $this;
    }

    /**
     * @return int
     */
    public function getPaymentCreditReward()
    {
        return $this->paymentCreditReward;
    }

    /**
     * @param int $paymentCreditReward
     *
     * @return $this
     */
    public function setPaymentCreditReward($paymentCreditReward)
    {
        $this->paymentCreditReward = $paymentCreditReward;
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
     * @return $this
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return int
     */
    public function getLocationRadius()
    {
        return $this->locationRadius;
    }

    /**
     * @param int $locationRadius
     * @return BaseCost;
     */
    public function setLocationRadius($locationRadius)
    {
        $this->locationRadius = $locationRadius;
        return $this;
    }
}
