<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Event\HeartbeatEvent;
use App\Event\GameOverEvent;
use App\Event\UserClosedEvent;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class Engine
{

    const FRAMES_PER_SEC = 60;

    const ONE_SEC_MICRO = 1000000;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var bool
     */
    private $gameOver = false;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run()
    {
        declare(ticks = 1);
        pcntl_signal(SIGINT, [$this, 'userClosed']);
        pcntl_signal(SIGTERM, [$this, 'userClosed']);

        while (!$this->gameOver) {
            $timeStart = microtime(true);
            $this->eventDispatcher->dispatch(new HeartbeatEvent(microtime(true)));
            $timeEnd = microtime(true);
            $time = $timeEnd - $timeStart;
            $timeToSleep = (self::ONE_SEC_MICRO / self::FRAMES_PER_SEC) - $time * self::ONE_SEC_MICRO;

            if ($timeToSleep > 0) {
                usleep($timeToSleep);
            }
        }
    }

    public function userClosed()
    {
        $this->eventDispatcher->dispatch(new UserClosedEvent());
        $this->gameOver = true;
    }

    /**
     * @param GameOverEvent $event
     */
    #[AsEventListener]
    public function gameOver(GameOverEvent $event)
    {
        $this->gameOver = true;
    }
}
