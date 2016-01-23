<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\UserRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="disc", type="smallint")
 * @ORM\DiscriminatorMap({
 *      0 = "FunPro\UserBundle\Entity\User",
 *      User::TYPE_DRIVER = "FunPro\DriverBundle\Entity\Driver",
 *      User::TYPE_PASSENGER = "FunPro\PassengerBundle\Entity\Passenger",
 * })
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class User extends BaseUser
{
    const SEX_MALE = 0;
    const SEX_FEMALE = 1;

    const TYPE_ADMIN = 1;
    const TYPE_OPERATOR = 2;
    const TYPE_MARKETER = 3;
    const TYPE_AGENCY = 4;
    const TYPE_DRIVER = 5;
    const TYPE_PASSENGER = 6;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     */
    protected $name;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $age;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     */
    protected $sex;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $describtion;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $avatar;

    /**
     * @var File
     */
    protected $avatarFile;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_at", referencedColumnName="id", onDelete="SET NULL")
     * @Gedmo\Blameable(on="create")
     */
    protected $createdBy;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_by", type="datetime")
     * @Gedmo\Timestampable(on="create")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    protected $deletedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="deleted_by", referencedColumnName="id", onDelete="SET NULL")
     * @Gedmo\Blameable(on="change", field="deletedAt")
     */
    protected $deletedBy;
    
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set age
     *
     * @param integer $age
     * @return User
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age
     *
     * @return integer 
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set sex
     *
     * @param integer $sex
     * @return User
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * Get sex
     *
     * @return integer 
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * Set describtion
     *
     * @param string $describtion
     * @return User
     */
    public function setDescribtion($describtion)
    {
        $this->describtion = $describtion;

        return $this;
    }

    /**
     * Get describtion
     *
     * @return string 
     */
    public function getDescribtion()
    {
        return $this->describtion;
    }

    /**
     * Set avatar
     *
     * @param string $avatar
     * @return User
     */
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get avatar
     *
     * @return string 
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return User
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
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
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return User
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set createdBy
     *
     * @param \FunPro\UserBundle\Entity\User $createdBy
     * @return User
     */
    public function setCreatedBy(\FunPro\UserBundle\Entity\User $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \FunPro\UserBundle\Entity\User 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set deletedBy
     *
     * @param \FunPro\UserBundle\Entity\User $deletedBy
     * @return User
     */
    public function setDeletedBy(\FunPro\UserBundle\Entity\User $deletedBy = null)
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * Get deletedBy
     *
     * @return \FunPro\UserBundle\Entity\User 
     */
    public function getDeletedBy()
    {
        return $this->deletedBy;
    }
}
