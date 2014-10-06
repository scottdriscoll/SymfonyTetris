<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\TetrisBundle\Event\RedrawEvent;
use SD\Game\Block\AbstractBlock;
use SD\Game\NextBlockManager;
use SD\TetrisBundle\Event\KeyboardDownEvent;
use SD\TetrisBundle\Event\KeyboardLeftEvent;
use SD\TetrisBundle\Event\KeyboardRightEvent;
use SD\TetrisBundle\Event\KeyboardRotateEvent;
use SD\Game\GameBoard;

/**
 * @DI\Service("game.active_block_manager")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ActiveBlockManager
{
    /**
     * @var AbstractBlock
     */
    private $activeBlock;

    /**
     * @var NextBlockManager
     */
    private $nextBlockManager;

    /**
     * @var GameBoard
     */
    private $gameBoard;

    /**
     * @var int
     */
    private $horizontalScale;

    /**
     * @var int
     */
    private $width;

    /**
     *  @DI\InjectParams({
     *     "gameBoard" = @DI\Inject("game.game_board"),
     *     "nextBlockManager" = @DI\Inject("game.next_block_manager"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "horizontalScale" = @DI\Inject("%horizontal_scale%")
     * })
     *
     * @param GameBoard $gameBoard
     * @param NextBlockManager $nextBlockManager
     * @param int $width;
     * @param int $horizontalScale
     */
    public function __construct(GameBoard $gameBoard, NextBlockManager $nextBlockManager, $width, $horizontalScale)
    {
        $this->gameBoard = $gameBoard;
        $this->nextBlockManager = $nextBlockManager;
        $this->width = $width;
        $this->horizontalScale = $horizontalScale;
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function heartbeat(HeartbeatEvent $event)
    {
        if (null === $this->activeBlock) {
            $this->activeBlock = $this->nextBlockManager->getNextBlock();
            if ($this->activeBlock) {
                $this->activeBlock->setXPosition($this->width / 2);
            }
        }
    }

    /**
     * @DI\Observe(Events::REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
    public function draw(RedrawEvent $event)
    {
        if ($this->activeBlock) {
            $this->activeBlock->draw($event->getOutput(), $this->horizontalScale);
        }
    }

    /**
     * @DI\Observe(Events::KEYBOARD_ROTATE, priority = 0)
     *
     * @param KeyboardRotateEvent $event
     */
    public function rotate(KeyboardRotateEvent $event)
    {
        $block = clone $this->activeBlock;
        $block->rotate();

        if ($this->gameBoard->doesBlockFix($block)) {
            $this->activeBlock->rotate();
        }
    }

    /**
     * @DI\Observe(Events::KEYBOARD_LEFT, priority = 0)
     *
     * @param KeyboardLeftEvent $event
     */
    public function moveLeft(KeyboardLeftEvent $event)
    {
        $block = clone $this->activeBlock;
        $block->setXPosition($block->getXPosition() - 1);

        if ($this->gameBoard->doesBlockFix($block)) {
            $this->activeBlock->setXPosition($this->activeBlock->getXPosition() - 1);
        }
    }

    /**
     * @DI\Observe(Events::KEYBOARD_RIGHT, priority = 0)
     *
     * @param KeyboardRightEvent $event
     */
    public function moveRight(KeyboardRightEvent $event)
    {
        $block = clone $this->activeBlock;
        $block->setXPosition($block->getXPosition() + 1);

        if ($this->gameBoard->doesBlockFix($block)) {
            $this->activeBlock->setXPosition($this->activeBlock->getXPosition() + 1);
        }
    }

    /**
     * @DI\Observe(Events::KEYBOARD_DOWN, priority = 0)
     *
     * @param KeyboardDownEvent $event
     */
    public function moveDown(KeyboardDownEvent $event)
    {

    }
}
