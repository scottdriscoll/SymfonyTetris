<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\GameSockets\Message;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ConnectionMessage extends AbstractMessage
{
    /**
     * @var string
     */
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
