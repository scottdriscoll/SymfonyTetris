<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameOverEvent extends Event
{
    const SOURCE_SELF = 'self';

    const SOURCE_PEER = 'peer';

    /**
     * @var string
     */
    private $source;

    /**
     * @param $source
     */
    public function __construct($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
