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
     * @param string $name
     */
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
