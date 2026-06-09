<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Event;

use Symfony\Contracts\EventDispatcher\Event;
use App\Game\Sockets\Message\BoardUpdateMessage;

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
