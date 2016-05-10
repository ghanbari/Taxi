<?php

namespace FunPro\EngineBundle\GCM;

use JMS\Serializer\Annotation as JS;

class Success
{
    /**
     * @var integer
     *
     * @JS\SerializedName("multicast_id")
     * @JS\Type("integer")
     */
    private $multicastId;

    /**
     * @var integer
     *
     * @JS\Type("integer")
     */
    private $success;

    /**
     * @var integer
     *
     * @JS\Type("integer")
     */
    private $failure;

    /**
     * @var integer
     *
     * @JS\SerializedName("canonical_ids")
     *
     * @JS\Type("integer")
     */
    private $canonicalIds;

    /**
     * @var array
     *
     * @JS\Type("array")
     */
    private $results;

    /**
     * @return int
     */
    public function getCanonicalIds()
    {
        return $this->canonicalIds;
    }

    /**
     * @param int $canonicalIds
     *
     * @return $this
     */
    public function setCanonicalIds($canonicalIds)
    {
        $this->canonicalIds = $canonicalIds;
        return $this;
    }

    /**
     * @return int
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * @param int $failure
     *
     * @return $this
     */
    public function setFailure($failure)
    {
        $this->failure = $failure;
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
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param array $results
     *
     * @return $this
     */
    public function setResults($results)
    {
        $this->results = $results;
        return $this;
    }

    /**
     * @return int
     */
    public function getSuccess()
    {
        return $this->success;
    }

    /**
     * @param int $success
     *
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;
        return $this;
    }
} 