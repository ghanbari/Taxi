<?php

namespace FunPro\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 * @ORM\HasLifecycleCallbacks()
 */
class Message
{
    /**
     * Message database id
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="FunPro\UserBundle\Entity\Device", inversedBy="messages")
     */
    protected $device;

    /**
     * @var string
     */
    protected $status = MessageStatus::MESSAGE_STATUS_NOT_SENT;

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @var string
     */
    protected $title = "";

    /**
     * @var string
     */
    protected $sound = null;

    /**
     * @var boolean
     */
    protected $contentAvailable = false;

    /**
     * @var array
     */
    protected $customData = array();

    /**
     * Collapse key for data
     *
     * @var string
     */
    protected $collapseKey = self::DEFAULT_COLLAPSE_KEY;

    /**
     * Options for GCM messages
     *
     * @var array
     */
    protected $gcmOptions = array();

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
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
     */
    protected $expiry = 604800;

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
}