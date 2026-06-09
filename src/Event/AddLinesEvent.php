<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;

use App\Game\Block\AbstractBlock;

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
