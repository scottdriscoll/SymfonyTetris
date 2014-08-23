<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;

/**
 * @DI\Service("game.engine")
 *
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

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function run()
    {
        declare(ticks = 1);
        pcntl_signal(SIGINT, [$this, 'gameOver']);
        pcntl_signal(SIGTERM, [$this, 'gameOver']);

        while (!$this->gameOver) {
            $timeStart = microtime(true);
            $this->eventDispatcher->dispatch(Events::HEARTBEAT, new HeartbeatEvent(microtime(true)));
            $timeEnd = microtime(true);
            $time = $timeEnd - $timeStart;
            $timeToSleep = (self::ONE_SEC_MICRO / self::FRAMES_PER_SEC) - $time * self::ONE_SEC_MICRO;

            if ($timeToSleep > 0) {
                usleep($timeToSleep);
            }
        }
    }

    /**
     * @DI\Observe(Events::GAME_OVER, priority = 0)
     */
    public function gameOver()
    {
        $this->gameOver = true;
    }
}
