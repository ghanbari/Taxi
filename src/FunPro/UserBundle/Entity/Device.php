<?php

namespace FunPro\UserBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JS;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="device",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="device_identifier_UNIQUE", columns={"device_identifier", "app_name"})}
 * )
 * @ORM\HasLifecycleCallbacks()
 *
 * @UniqueEntity(fields={"deviceIdentifier", "appName"})
 */
class Device
{
    const STATUS_DEACTIVE = 'deactive';
    const STATUS_ACTIVE = 'active';
    const STATUS_UNKNOWN = 'unknown';

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $id;
    
    /**
     * @var string Device token
     *
     * @ORM\Column(name="device_token", type="text")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="4097")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $deviceToken;

    /**
     * @var string
     *
     * @ORM\Column(name="device_identifier", length=50)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="50")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $deviceIdentifier;
    
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_sound_allowed")
     *
     * @Assert\Type("boolean")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $soundAllowed = false;
    
    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", name="is_alert_allowed")
     *
     * @Assert\Type("boolean")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $alertAllowed = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="device_name")
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="100")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $deviceName;
    
    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="10")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $os;
    
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=20)
     *
     * @Assert\Choice(callback="getStatusAvailable")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $status = self::STATUS_ACTIVE;
    
    /**
     * @var string
     *
     * @ORM\Column(name="device_model", length=100)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="100")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $deviceModel;
    
    /**
     * @var string
     *
     * @ORM\Column(name="device_version", length=30)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="30")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $deviceVersion;

    /**
     * @var String $appName application package name
     *
     * @ORM\Column(name="app_name", length=50)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="50")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $appName;
    
    /**
     * @var string
     *
     * @ORM\Column(name="app_version", length=10)
     *
     * @Assert\NotBlank()
     * @Assert\Length(max="10")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $appVersion;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_at")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $createdAt;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="updated_at")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $updatedAt;
    
    /**
     * @ORM\OneToMany(targetEntity="FunPro\UserBundle\Entity\Message", mappedBy="device")
     *
     * @JS\Groups({"Messages"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $messages;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", unique=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $apiKey;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\User", inversedBy="devices")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="cascade")
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\MaxDepth(1)
     * @JS\Since("1.0.0")
     */
    private $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_login_at", type="datetime", nullable=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $lastLoginAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public static function getStatusAvailable()
    {
        return array(
            self::STATUS_ACTIVE,
            self::STATUS_DEACTIVE,
        );
    }

    /**
     * @ORM\PrePersist
     */
    public function PrePersist() {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();

    }

    /**
     * @ORM\PreUpdate
     */
    public function PreUpdate() {
        $this->updatedAt = new \DateTime();
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
     * Set deviceToken
     *
     * @param string $deviceToken
     * @return Device
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;

        return $this;
    }

    /**
     * Get deviceToken
     *
     * @return string 
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
    }

    /**
     * Set deviceIdentifier
     *
     * @param string $deviceIdentifier
     * @return Device
     */
    public function setDeviceIdentifier($deviceIdentifier)
    {
        $this->deviceIdentifier = $deviceIdentifier;

        return $this;
    }

    /**
     * Get deviceIdentifier
     *
     * @return string 
     */
    public function getDeviceIdentifier()
    {
        return $this->deviceIdentifier;
    }

    /**
     * Set soundAllowed
     *
     * @param boolean $soundAllowed
     * @return Device
     */
    public function setSoundAllowed($soundAllowed)
    {
        $this->soundAllowed = $soundAllowed;

        return $this;
    }

    /**
     * Get soundAllowed
     *
     * @return boolean 
     */
    public function getSoundAllowed()
    {
        return $this->soundAllowed;
    }

    /**
     * Set alertAllowed
     *
     * @param boolean $alertAllowed
     * @return Device
     */
    public function setAlertAllowed($alertAllowed)
    {
        $this->alertAllowed = $alertAllowed;

        return $this;
    }

    /**
     * Get alertAllowed
     *
     * @return boolean 
     */
    public function getAlertAllowed()
    {
        return $this->alertAllowed;
    }

    /**
     * Set deviceName
     *
     * @param string $deviceName
     * @return Device
     */
    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;

        return $this;
    }

    /**
     * Get deviceName
     *
     * @return string 
     */
    public function getDeviceName()
    {
        return $this->deviceName;
    }

    /**
     * Set os
     *
     * @param string $os
     * @return Device
     */
    public function setOs($os)
    {
        $this->os = $os;

        return $this;
    }

    /**
     * Get os
     *
     * @return string 
     */
    public function getOs()
    {
        return $this->os;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Device
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set deviceModel
     *
     * @param string $deviceModel
     * @return Device
     */
    public function setDeviceModel($deviceModel)
    {
        $this->deviceModel = $deviceModel;

        return $this;
    }

    /**
     * Get deviceModel
     *
     * @return string 
     */
    public function getDeviceModel()
    {
        return $this->deviceModel;
    }

    /**
     * @return String
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     * @param String $appName
     *
     * @return $this
     */
    public function setAppName($appName)
    {
        $this->appName = $appName;
        return $this;
    }

    /**
     * Set deviceVersion
     *
     * @param string $deviceVersion
     * @return Device
     */
    public function setDeviceVersion($deviceVersion)
    {
        $this->deviceVersion = $deviceVersion;

        return $this;
    }

    /**
     * Get deviceVersion
     *
     * @return string 
     */
    public function getDeviceVersion()
    {
        return $this->deviceVersion;
    }

    /**
     * Set appVersion
     *
     * @param string $appVersion
     * @return Device
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;

        return $this;
    }

    /**
     * Get appVersion
     *
     * @return string 
     */
    public function getAppVersion()
    {
        return $this->appVersion;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Device
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
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return Device
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

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
     * Set apiKey
     *
     * @param string $apiKey
     * @return Device
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string 
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Add messages
     *
     * @param Message $messages
     * @return Device
     */
    public function addMessage(Message $messages)
    {
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param Message $messages
     */
    public function removeMessage(Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Set owner
     *
     * @param User $owner
     * @return Device
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return User 
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @return \DateTime
     */
    public function getLastLoginAt()
    {
        return $this->lastLoginAt;
    }

    /**
     * @param \DateTime $lastLoginAt
     */
    public function setLastLoginAt($lastLoginAt)
    {
        $this->lastLoginAt = $lastLoginAt;
    }
}
