<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

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
