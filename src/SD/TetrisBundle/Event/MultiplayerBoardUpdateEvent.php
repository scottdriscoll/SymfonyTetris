<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use SD\Game\Sockets\Message\BoardUpdateMessage;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class MultiplayerBoardUpdateEvent extends Event
{
    /**
     * @var BoardUpdateMessage
     */
    private $message;

    /**
     * @param BoardUpdateMessage $message
     */
    public function __construct(BoardUpdateMessage $message)
    {
        $this->message = $message;
    }

    /**
     * @return BoardUpdateMessage
     */
    public function getMessage()
    {
        return $this->message;
    }
}
