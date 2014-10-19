<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Sockets\Message;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class AddLinesMessage extends AbstractMessage
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
