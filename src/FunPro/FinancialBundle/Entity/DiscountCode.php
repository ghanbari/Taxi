<?php

namespace FunPro\FinancialBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FunPro\GeoBundle\Doctrine\ValueObject\Point;
use FunPro\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;

/**
 * DiscountCode
 *
 * @ORM\Table(name="discount_code")
 * @ORM\Entity(repositoryClass="FunPro\FinancialBundle\Repository\DiscountCodeRepository")
 *
 * @Gedmo\SoftDeleteable(fieldName="expiredAt", timeAware=true)
 */
class DiscountCode
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
     * @var string
     *
     * @ORM\Column(name="title")
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin", "Passenger"})
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="code", length=10, unique=true)
     *
     * @Assert\NotBlank(groups={"Create", "Update"})
     * @Assert\Length(min="7", max="7", groups={"Create", "Update"})
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin", "Passenger"})
     */
    private $code;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_usage", type="integer")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type("numeric", groups={"Create", "Update"}))
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $maxUsage;

    /**
     * @var integer
     *
     * @ORM\Column(name="max_usage_per_user", type="integer")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type("numeric", groups={"Create", "Update"}))
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $maxUsagePerUser;

    /**
     * @var Point
     *
     * @ORM\Column(type="point")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type("FunPro\GeoBundle\Doctrine\ValueObject\Point", groups={"Create", "Update"})
     * @Assert\Valid()
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $originLocation;

    /**
     * @var integer
     *
     * @ORM\Column(name="location_radius", type="integer")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type("numeric", groups={"Create", "Update"})
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $locationRadius;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotNull(groups={"Create", "Update"})
     * @Assert\Type("numeric", groups={"Create", "Update"})
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin", "Passenger"})
     */
    private $discount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expired_at", type="date")
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin", "Passenger"})
     */
    private $expiredAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Gedmo\Blameable(on="create")
     *
     * @JS\Since("1.0.0")
     * @JS\Groups({"Admin"})
     */
    private $createdBy;

    /**
     * DiscountCode constructor.
     */
    public function __construct()
    {
        $this->maxUsage = 0;
        $this->maxUsagePerUser = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return DiscountCode;
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return DiscountCode;
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxUsage()
    {
        return $this->maxUsage;
    }

    /**
     * @param int $maxUsage
     * @return DiscountCode;
     */
    public function setMaxUsage($maxUsage)
    {
        $this->maxUsage = $maxUsage;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxUsagePerUser()
    {
        return $this->maxUsagePerUser;
    }

    /**
     * @param int $maxUsagePerUser
     * @return DiscountCode;
     */
    public function setMaxUsagePerUser($maxUsagePerUser)
    {
        $this->maxUsagePerUser = $maxUsagePerUser;
        return $this;
    }

    /**
     * @return Point
     */
    public function getOriginLocation()
    {
        return $this->originLocation;
    }

    /**
     * @param Point $originLocation
     * @return DiscountCode;
     */
    public function setOriginLocation($originLocation)
    {
        $this->originLocation = $originLocation;
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
     * @return DiscountCode;
     */
    public function setLocationRadius($locationRadius)
    {
        $this->locationRadius = $locationRadius;
        return $this;
    }

    /**
     * @return int
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @param int $discount
     * @return DiscountCode;
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
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
     * @return DiscountCode;
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime $expiredAt
     * @return DiscountCode;
     */
    public function setExpiredAt($expiredAt)
    {
        $this->expiredAt = $expiredAt;
        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $createdBy
     * @return DiscountCode;
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return DiscountCode;
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }
}
