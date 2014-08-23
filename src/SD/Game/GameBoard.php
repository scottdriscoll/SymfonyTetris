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
 * @DI\Service("game.game_board")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameBoard
{
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $board = [];

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "height" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param int $width
     * @param int $height
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $width, $height)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->width = $width;
        $this->height = $height;

        for ($h = 0; $h < $height; $h++) {
            for ($w = 0; $w < $width; $w++) {
                $this->board[$h][$w] = ' ';
            }
        }
    }

}
