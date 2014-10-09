<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\GameSockets\Message;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
abstract class AbstractMessage
{
    /**
     * @var string
     */
    private $objectId;

    /**
     * @var bool
     */
    private $critical = false;

    /**
     * @return string
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param string $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * @return boolean
     */
    public function isCritical()
    {
        return $this->critical;
    }

    /**
     * @param boolean $critical
     */
    public function setCritical($critical)
    {
        $this->critical = $critical;
    }
}
