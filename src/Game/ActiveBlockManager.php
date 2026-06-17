<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Event\HeartbeatEvent;
use App\Event\RedrawEvent;
use App\Game\Block\AbstractBlock;
use App\Event\KeyboardDownEvent;
use App\Event\KeyboardLeftEvent;
use App\Event\KeyboardRightEvent;
use App\Event\KeyboardRotateEvent;
use App\Event\BlockReachedBottomEvent;
use App\Event\GameOverEvent;
use App\Event\StageClearedEvent;
use App\Event\BlockMovedEvent;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ActiveBlockManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

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
     * @var int
     */
    private $lastUpdate = 0;

    /**
     * @var float
     */
    private $fallDelay = 1.0;

    public function __construct(EventDispatcherInterface $eventDispatcher, GameBoard $gameBoard, NextBlockManager $nextBlockManager, $width, $horizontalScale)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->gameBoard = $gameBoard;
        $this->nextBlockManager = $nextBlockManager;
        $this->width = $width;
        $this->horizontalScale = $horizontalScale;
    }

    /**
     * @param HeartbeatEvent $event
     */
    #[AsEventListener]
    public function heartbeat(HeartbeatEvent $event)
    {
        $blockMoved = false;

        if (null === $this->activeBlock) {
            $blockMoved = true;
            $this->activeBlock = $this->nextBlockManager->getNextBlock();
            if ($this->activeBlock) {
                $this->activeBlock->setXPosition($this->width / 2);
                if (!$this->gameBoard->doesBlockFit($this->activeBlock)) {
                    $this->eventDispatcher->dispatch(new GameOverEvent(GameOverEvent::SOURCE_SELF));
                }
            }
        } else {
            if ($event->getTimestamp() > $this->lastUpdate + $this->fallDelay) {
                $this->lastUpdate = $event->getTimestamp();

                // Test if we reached the bottom or another block
                $block = clone $this->activeBlock;
                $block->setYPosition($block->getYPosition() + 1);

                if (!$this->gameBoard->doesBlockFit($block)) {
                    $this->eventDispatcher->dispatch(new BlockReachedBottomEvent($this->activeBlock));
                    $this->activeBlock = null;
                } else {
                    $this->activeBlock->setYPosition($this->activeBlock->getYPosition() + 1);
                    $blockMoved = true;
                }
            }
        }

        if ($blockMoved && $this->activeBlock) {
            $this->eventDispatcher->dispatch(new BlockMovedEvent($this->activeBlock));
        }
    }

    /**
     * @param RedrawEvent $event
     */
    #[AsEventListener]
    public function draw(RedrawEvent $event)
    {
        if ($this->activeBlock) {
            if ($this->gameBoard->isLandingPreviewEnabled()) {
                $landingPreviewBlock = $this->gameBoard->getLandingPreviewBlock($this->activeBlock);
                $landingPreviewBlock->draw($event->getOutput(), $this->horizontalScale, 'gray');
            }

            $this->activeBlock->draw($event->getOutput(), $this->horizontalScale);
        }
    }

    /**
     * @param KeyboardRotateEvent $event
     */
    #[AsEventListener]
    public function rotate(KeyboardRotateEvent $event)
    {
        if (null === $this->activeBlock) {
            return;
        }
        $block = clone $this->activeBlock;
        $block->rotate();

        if ($this->gameBoard->doesBlockFit($block)) {
            $this->activeBlock = $block;
            $this->eventDispatcher->dispatch(new BlockMovedEvent($this->activeBlock));
        }
    }

    /**
     * @param KeyboardLeftEvent $event
     */
    #[AsEventListener]
    public function moveLeft(KeyboardLeftEvent $event)
    {
        if (null === $this->activeBlock) {
            return;
        }
        $block = clone $this->activeBlock;
        $block->setXPosition($block->getXPosition() - 1);

        if ($this->gameBoard->doesBlockFit($block)) {
            $this->activeBlock = $block;
            $this->eventDispatcher->dispatch(new BlockMovedEvent($this->activeBlock));
        }
    }

    /**
     * @param KeyboardRightEvent $event
     */
    #[AsEventListener]
    public function moveRight(KeyboardRightEvent $event)
    {
        if (null === $this->activeBlock) {
            return;
        }
        $block = clone $this->activeBlock;
        $block->setXPosition($block->getXPosition() + 1);

        if ($this->gameBoard->doesBlockFit($block)) {
            $this->activeBlock = $block;
            $this->eventDispatcher->dispatch(new BlockMovedEvent($this->activeBlock));
        }
    }

    /**
     * @param KeyboardDownEvent $event
     */
    #[AsEventListener]
    public function moveDown(KeyboardDownEvent $event)
    {
        if (null === $this->activeBlock) {
            return;
        }
        $block = clone $this->activeBlock;
        $block->setYPosition($block->getYPosition() + 1);

        if (!$this->gameBoard->doesBlockFit($block)) {
            $this->eventDispatcher->dispatch(new BlockReachedBottomEvent($this->activeBlock));
            $this->activeBlock = null;
        } else {
            $this->activeBlock = $block;
            $this->eventDispatcher->dispatch(new BlockMovedEvent($this->activeBlock));
        }
    }

    /**
     * @param StageClearedEvent $event
     */
    #[AsEventListener]
    public function onStageCleared(StageClearedEvent $event)
    {
        if ($this->fallDelay > 0.3) {
            $this->fallDelay -= 0.2;
        }
    }

    /**
     * @return AbstractBlock
     */
    public function getActiveBlock()
    {
        return $this->activeBlock;
    }
}
