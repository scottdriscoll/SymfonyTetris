<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Sockets\Message;

use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class CriticalMessage
{
    const STOPWATCH_NAME = 'critical';

    /**
     * @var AbstractMessage
     */
    private $message;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @param AbstractMessage $message
     */
    public function __construct(AbstractMessage $message = null)
    {
        $this->message = $message;
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start(self::STOPWATCH_NAME);
    }

    /**
     * @return int elapsed time in milliseconds
     */
    public function getElapsedTime()
    {
        $event = $this->stopwatch->getEvent(self::STOPWATCH_NAME);

        return $event->getDuration();
    }

    /**
     * @return AbstractMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param AbstractMessage $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function restartTimer()
    {
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start(self::STOPWATCH_NAME);
    }
}
