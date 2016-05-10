<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as JS;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';

    /**
     * Message database id
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="gcm_id", type="string", nullable=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $gcmId;

    /**
     * @var integer
     *
     * @ORM\Column(name="multicast_id", type="integer", nullable=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $multicastId;

    /**
     * @var Device
     *
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\Device", inversedBy="messages")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $device;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="smallint")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $status = 0;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $error;

    /**
     * @var array
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("registration_ids")
     */
    protected $registrationIds;

    /**
     * Collapse key for data
     *
     * @var string
     *
     * @ORM\Column(name="collapse_key", nullable=true)
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("collapse_key")
     */
    protected $collapseKey;

    /**
     * @var string
     *
     * @ORM\Column(name="priority", length=6)
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("priority")
     */
    protected $priority = self::PRIORITY_NORMAL;

    /**
     * @var boolean
     *
     * @ORM\Column(name="content_available", type="boolean", options={"default"=false})
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("content_available")
     */
    protected $contentAvailable = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="delay_while_idle", type="boolean", options={"default"=false})
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("delay_while_idle")
     */
    protected $delayWhileIdle = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="time_to_live", type="integer")
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("time_to_live")
     */
    protected $timeToLive = 86400;

    /**
     * @var string
     *
     * @ORM\Column(name="restricted_package_name", nullable=true)
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("restricted_package_name")
     */
    protected $restrictedPackageName;

    /**
     * @var boolean
     *
     * @ORM\Column(name="dry_run", type="boolean", options={"default"=false})
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("dry_run")
     */
    protected $dryRun = false;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="array", nullable=true)
     *
     * @JS\Groups({"Public", "GCM"})
     * @JS\Since("1.0.0")
     * @JS\SerializedName("data")
     */
    protected $data;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $body;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $icon;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $sound;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $badge;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $tag;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $color;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    protected $click_action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $updatedAt;

    /**
     * Expiration date (UTC)
     *
     * A fixed UNIX epoch date expressed in seconds (UTC) that identifies when the notification is no longer valid and can be discarded.
     * If the expiry value is non-zero, APNs tries to deliver the notification at least once.
     * Specify zero to request that APNs not store the notification at all.
     *
     * @var int
     *
     * @ORM\Column(name="expiry", type="integer")
     *
     * @JS\Groups({"Public"})
     * @JS\Since("1.0.0")
     */
    protected $expiry = 604800;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function PreUpdate() {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getGcmId()
    {
        return $this->gcmId;
    }

    /**
     * @param int $gcmId
     *
     * @return $this
     */
    public function setGcmId($gcmId)
    {
        $this->gcmId = $gcmId;
        return $this;
    }

    /**
     * @return int
     */
    public function getMulticastId()
    {
        return $this->multicastId;
    }

    /**
     * @param int $multicastId
     *
     * @return $this
     */
    public function setMulticastId($multicastId)
    {
        $this->multicastId = $multicastId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBadge()
    {
        return $this->badge;
    }

    /**
     * @param string $badge
     *
     * @return $this
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getClickAction()
    {
        return $this->click_action;
    }

    /**
     * @param string $click_action
     *
     * @return $this
     */
    public function setClickAction($click_action)
    {
        $this->click_action = $click_action;
        return $this;
    }

    /**
     * @return string
     */
    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * @param string $collapseKey
     *
     * @return $this
     */
    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isContentAvailable()
    {
        return $this->contentAvailable;
    }

    /**
     * @param boolean $contentAvailable
     *
     * @return $this
     */
    public function setContentAvailable($contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;
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
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
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
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDelayWhileIdle()
    {
        return $this->delayWhileIdle;
    }

    /**
     * @param boolean $delayWhileIdle
     *
     * @return $this
     */
    public function setDelayWhileIdle($delayWhileIdle)
    {
        $this->delayWhileIdle = $delayWhileIdle;
        return $this;
    }

    /**
     * @return Device
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param Device $device
     *
     * @return $this
     */
    public function setDevice($device)
    {
        $this->device = $device;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDryRun()
    {
        return $this->dryRun;
    }

    /**
     * @param boolean $dryRun
     *
     * @return $this
     */
    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * @return int
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * @param int $expiry
     *
     * @return $this
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
        return $this;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param string $icon
     *
     * @return $this
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @JS\VirtualProperty()
     * @JS\SerializedName("notification")
     * @JS\Groups("GCM")
     * @JS\Type("array<string, string>")
     *
     * @return array
     */
    public function getNotification()
    {
        return array(
            'title' => $this->getTitle(),
            'body' => $this->getBadge(),
            'icon' => $this->getIcon(),
            'sound' => $this->getSound(),
            'badge' => $this->getBadge(),
            'tag' => $this->getTag(),
            'color' => $this->getColor(),
            'click_action' => $this->getClickAction(),
        );
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param string $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return array
     */
    public function getRegistrationIds()
    {
        return $this->registrationIds;
    }

    /**
     * @param array $registrationIds
     *
     * @return $this
     */
    public function setRegistrationIds(array $registrationIds=array())
    {
        $this->registrationIds = $registrationIds;
        return $this;
    }

    /**
     * add a register id to message
     *
     * @param $registrationIds
     * @return $this
     */
    public function addRegistrationIds($registrationIds)
    {
        $this->registrationIds[] = $registrationIds;
        return $this;
    }

    /**
     * @return string
     */
    public function getRestrictedPackageName()
    {
        return $this->restrictedPackageName;
    }

    /**
     * @param string $restrictedPackageName
     *
     * @return $this
     */
    public function setRestrictedPackageName($restrictedPackageName)
    {
        $this->restrictedPackageName = $restrictedPackageName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * @param string $sound
     *
     * @return $this
     */
    public function setSound($sound)
    {
        $this->sound = $sound;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     *
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $tag
     *
     * @return $this
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimeToLive()
    {
        return $this->timeToLive;
    }

    /**
     * @param int $timeToLive
     *
     * @return $this
     */
    public function setTimeToLive($timeToLive)
    {
        $this->timeToLive = $timeToLive;
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
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}