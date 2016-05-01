<?php

namespace FunPro\AgentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;
use JMS\Serializer\Annotation as JS;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Agent
 *
 * @ORM\Table(name="agent")
 * @ORM\Entity(repositoryClass="FunPro\AgentBundle\Repository\AgentRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(type="smallint", name="disc")
 * @ORM\DiscriminatorMap({
 *      0 = "FunPro\AgentBundle\Entity\Agent",
 *      Agent::TYPE_AGENCY = "FunPro\AgentBundle\Entity\Agency"
 * })
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Agent
{
    const TYPE_AGENCY = 1;

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
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="admin_id", referencedColumnName="id", onDelete="cascade", nullable=false)
     *
     * @JS\Groups({"AgencyAdmin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    protected $admin;

    /**
     * @var array
     *
     * @ORM\OneToOne(targetEntity="FunPro\GeoBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="RESTRICT", nullable=false)
     *
     * @JS\Groups({"AgencyAddress"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    protected $address;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     *
     * @JS\Groups({"AgencyContact"})
     * @JS\Since("1.0.0")
     */
    protected $contacts;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $description;

    #TODO: map this property.
    protected $documents;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     *
     * @JS\Groups({"Private"})
     * @JS\Since("1.0.0")
     */
    protected $deletedAt;

    public function __construct()
    {
        $this->contacts = array();
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
     * Set contacts
     *
     * @param array $contacts
     * @return Agent
     */
    public function setContacts(array $contacts)
    {
        $this->contacts = $contacts;

        return $this;
    }

    /**
     * Get contacts
     *
     * @return array 
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Agent
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set admin
     *
     * @param \FunPro\UserBundle\Entity\User $admin
     * @return Agent
     */
    public function setAdmin(\FunPro\UserBundle\Entity\User $admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return \FunPro\UserBundle\Entity\User 
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set address
     *
     * @param \FunPro\GeoBundle\Entity\Address $address
     * @return Agent
     */
    public function setAddress(\FunPro\GeoBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return \FunPro\GeoBundle\Entity\Address 
     */
    public function getAddress()
    {
        return $this->address;
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
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}
