<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Sockets\Message;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class AcknowledgeMessage extends AbstractMessage
{
    /**
     * @var string
     */
    private $acknowledgedMessageId;

    /**
     * @param string $acknowledgedMessageId
     */
    public function __construct($acknowledgedMessageId)
    {
        $this->acknowledgedMessageId = $acknowledgedMessageId;
    }

    /**
     * @return string
     */
    public function getAcknowledgedMessageId()
    {
        return $this->acknowledgedMessageId;
    }
}
