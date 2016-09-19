<?php

namespace FunPro\TicketBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ticket
 *
 * @ORM\Table(name="ticket")
 * @ORM\Entity(repositoryClass="FunPro\TicketBundle\Repository\TicketRepository")
 *
 * @Gedmo\Tree(type="nested")
 */
class Ticket
{
    const PRIORITY_LOW = 0;
    const PRIORITY_MEDIUM = 1;
    const PRIORITY_HIGH = 2;
    const PRIORITY_VERY_HIGH = 3;

    const TYPE_COMPLAINT = 1;
    const TYPE_PROPOSAL = 2;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Length(max="255", min="3", groups={"Create", "Update"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @Assert\Length(max="2000", min="5", groups={"Create", "Update"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $message;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\Choice(callback="getValidPriority", groups={"Create", "Update"})
     * @Assert\NotNull(groups={"Create", "Update"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $priority;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\Choice(callback="getValidType", groups={"Create", "Update"})
     * @Assert\NotNull(groups={"Create", "Update"})
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $type;

    /**
     * @var Ticket
     *
     * @ORM\ManyToOne(targetEntity="FunPro\TicketBundle\Entity\Ticket", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     * @Gedmo\TreeParent()
     *
     * @Assert\Type(type="FunPro\TicketBundle\Entity\Ticket", groups={"Create", "Update"})
     *
     * @JS\Groups({"Parent"})
     * @JS\Since("1.0.0")
     */
    private $parent;

    /**
     * @var integer
     *
     * @ORM\Column(type="smallint", nullable=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeRight()
     *
     * @JS\Exclude()
     */
    private $rtl;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeLeft()
     *
     * @JS\Exclude()
     */
    private $lft;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Gedmo\TreeRoot()
     *
     * @JS\Exclude()
     */
    private $root;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @Gedmo\TreeLevel()
     *
     * @JS\Exclude()
     */
    private $lvl;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="FunPro\TicketBundle\Entity\Ticket", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     *
     * @JS\Groups({"Children"})
     * @JS\Since("1.0.0")
     */
    private $children;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Timestampable(on="create")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $updatedAt;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="array", nullable=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $data;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->data = array();
        $this->children = new ArrayCollection();
    }

    public static function getValidPriority()
    {
        return array(
            'high' => self::PRIORITY_HIGH,
            'medium' => self::PRIORITY_MEDIUM,
            'low' => self::PRIORITY_LOW,
        );
    }

    public static function getValidType()
    {
        return array(
            'complaint' => self::TYPE_COMPLAINT,
            'proposal' => self::TYPE_PROPOSAL,
        );
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
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Ticket
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return Ticket
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get priority
     *
     * @return integer
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Set priority
     *
     * @param integer $priority
     *
     * @return Ticket
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Ticket
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Ticket
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get rtl
     *
     * @return integer
     */
    public function getRtl()
    {
        return $this->rtl;
    }

    /**
     * Set rtl
     *
     * @param integer $rtl
     *
     * @return Ticket
     */
    public function setRtl($rtl)
    {
        $this->rtl = $rtl;

        return $this;
    }

    /**
     * Get lft
     *
     * @return integer
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     *
     * @return Ticket
     */
    public function setLft($lft)
    {
        $this->lft = $lft;

        return $this;
    }

    /**
     * Get root
     *
     * @return integer
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Set root
     *
     * @param integer $root
     *
     * @return Ticket
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     *
     * @return Ticket
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Ticket
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Ticket
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Ticket
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Ticket
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set parent
     *
     * @param Ticket $parent
     *
     * @return Ticket
     */
    public function setParent(Ticket $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Add children
     *
     * @param Ticket $children
     *
     * @return Ticket
     */
    public function addChild(Ticket $children)
    {
        $this->children[] = $children;

        return $this;
    }

    /**
     * Remove children
     *
     * @param Ticket $children
     */
    public function removeChild(Ticket $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}
