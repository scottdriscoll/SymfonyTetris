<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class PlayerConnectedEvent extends Event
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $peerName;

    /**
     * @param string $name
     * @param string $peerName
     */
    public function __construct($name, $peerName)
    {
        $this->name = $name;
        $this->peerName = $peerName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPeerName()
    {
        return $this->peerName;
    }
}
