<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="FunPro\UserBundle\Repository\DeviceRepository")
 * @ORM\Table(name="device")
 * @ORM\HasLifecycleCallbacks()
 *
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
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
     * @var \Datetime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

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
     * @var string
     *
     * @ORM\Column(name="play_service_version", length=100, nullable=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $playServiceVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="device_date_time", type="string", nullable=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $deviceDateTime;

    /**
     * @var string
     *
     * @ORM\Column(name="device_timezone", length=50, nullable=true)
     *
     * @JS\Groups({"Owner", "Admin"})
     * @JS\Since("1.0.0")
     */
    private $deviceTimezone;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->messages = new ArrayCollection();
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
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
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
     * Get deviceToken
     *
     * @return string
     */
    public function getDeviceToken()
    {
        return $this->deviceToken;
    }

    /**
     * Set deviceToken
     *
     * @param string $deviceToken
     *
     * @return Device
     */
    public function setDeviceToken($deviceToken)
    {
        $this->deviceToken = $deviceToken;

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
     * Set deviceIdentifier
     *
     * @param string $deviceIdentifier
     *
     * @return Device
     */
    public function setDeviceIdentifier($deviceIdentifier)
    {
        $this->deviceIdentifier = $deviceIdentifier;

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
     * Set soundAllowed
     *
     * @param boolean $soundAllowed
     *
     * @return Device
     */
    public function setSoundAllowed($soundAllowed)
    {
        $this->soundAllowed = $soundAllowed;

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
     * Set alertAllowed
     *
     * @param boolean $alertAllowed
     *
     * @return Device
     */
    public function setAlertAllowed($alertAllowed)
    {
        $this->alertAllowed = $alertAllowed;

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
     * Set deviceName
     *
     * @param string $deviceName
     *
     * @return Device
     */
    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;

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
     * Set os
     *
     * @param string $os
     *
     * @return Device
     */
    public function setOs($os)
    {
        $this->os = $os;

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
     * Set status
     *
     * @param string $status
     *
     * @return Device
     */
    public function setStatus($status)
    {
        $this->status = $status;

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
     * Set deviceModel
     *
     * @param string $deviceModel
     *
     * @return Device
     */
    public function setDeviceModel($deviceModel)
    {
        $this->deviceModel = $deviceModel;

        return $this;
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
     * Get deviceVersion
     *
     * @return string
     */
    public function getDeviceVersion()
    {
        return $this->deviceVersion;
    }

    /**
     * Set deviceVersion
     *
     * @param string $deviceVersion
     *
     * @return Device
     */
    public function setDeviceVersion($deviceVersion)
    {
        $this->deviceVersion = $deviceVersion;

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
     * Set appVersion
     *
     * @param string $appVersion
     *
     * @return Device
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;

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
     * @return Device
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
     * @return Device
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \Datetime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \Datetime $deletedAt
     *
     * @return $this
     */
    public function setDeletedAt(\Datetime $deletedAt)
    {
        $this->deletedAt = $deletedAt;
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
     * Set apiKey
     *
     * @param string $apiKey
     *
     * @return Device
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * Add messages
     *
     * @param Message $messages
     *
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
     * @return ArrayCollection
     */
    public function getMessages()
    {
        return $this->messages;
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
     * Set owner
     *
     * @param User $owner
     *
     * @return Device
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
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

    /**
     * @return string
     */
    public function getDeviceDateTime()
    {
        return $this->deviceDateTime;
    }

    /**
     * @param string $deviceDateTime
     *
     * @return $this
     */
    public function setDeviceDateTime($deviceDateTime)
    {
        $this->deviceDateTime = $deviceDateTime;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeviceTimezone()
    {
        return $this->deviceTimezone;
    }

    /**
     * @param string $deviceTimezone
     *
     * @return $this
     */
    public function setDeviceTimezone($deviceTimezone)
    {
        $this->deviceTimezone = $deviceTimezone;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlayServiceVersion()
    {
        return $this->playServiceVersion;
    }

    /**
     * @param string $playServiceVersion
     *
     * @return $this
     */
    public function setPlayServiceVersion($playServiceVersion)
    {
        $this->playServiceVersion = $playServiceVersion;
        return $this;
    }
}
