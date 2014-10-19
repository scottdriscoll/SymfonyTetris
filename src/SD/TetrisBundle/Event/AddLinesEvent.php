<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use SD\Game\Block\AbstractBlock;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class AddLinesEvent extends Event
{
    /**
     * @var int
     */
    private $lines;

    /**
     * @param int $lines
     */
    public function __construct($lines)
    {
        $this->lines = $lines;
    }

    /**
     * @return int
     */
    public function getLines()
    {
        return $this->lines;
    }
}
