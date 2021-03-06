<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class LinesClearedEvent extends Event
{
    /**
     * @var int
     */
    private $linesClearedCount;

    /**
     * @param int $linesClearedCount
     */
    public function __construct($linesClearedCount)
    {
        $this->linesClearedCount = $linesClearedCount;
    }

    /**
     * @return int
     */
    public function getLinesClearedCount()
    {
        return $this->linesClearedCount;
    }
}
