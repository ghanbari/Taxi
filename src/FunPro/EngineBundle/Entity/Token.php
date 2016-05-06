<?php

namespace FunPro\EngineBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FunPro\UserBundle\Entity\User;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;

/**
 * Token
 *
 * @ORM\Table(name="token")
 * @ORM\Entity(repositoryClass="FunPro\EngineBundle\Repository\TokenRepository")
 *
 * @JS\ExclusionPolicy("ALL")
 */
class Token
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
     * @var String
     *
     * @ORM\Column(length=10)
     */
    private $token;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User", inversedBy="tokens")
     */
    private $user;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_expired", type="boolean")
     */
    private $expired;

    public function __construct($token)
    {
        $this->token = $token;
        $this->expired = false;
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return String
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param String $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expired;
    }

    /**
     * @param boolean $expired
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;
    }
}
