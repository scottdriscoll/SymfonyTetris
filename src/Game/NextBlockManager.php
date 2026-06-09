<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Event\HeartbeatEvent;
use App\Event\RedrawEvent;
use App\Event\NextBlockReadyEvent;
use App\Game\Block\BlockFactory;
use App\Game\Block\AbstractBlock;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class NextBlockManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $horizontalScale;

    /**
     * @var AbstractBlock
     */
    private $nextBlock;

    /**
     * @var bool
     */
    private $erase = false;

    public function __construct(EventDispatcherInterface $eventDispatcher, BlockFactory $blockFactory, $width, $horizontalScale)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->blockFactory = $blockFactory;
        $this->width = $width;
        $this->horizontalScale = $horizontalScale;
    }

    /**
     * @param HeartbeatEvent $event
     */
    #[AsEventListener]
    public function heartbeat(HeartbeatEvent $event)
    {
        if (null === $this->nextBlock) {
            $this->nextBlock = $this->blockFactory->getRandomBlock();
            $this->nextBlock->setXPosition($this->width + 3);
            $this->nextBlock->setYPosition(1);
            $this->eventDispatcher->dispatch(new NextBlockReadyEvent($this->nextBlock));
        }
    }

    /**
     * @param RedrawEvent $event
     */
    #[AsEventListener]
    public function draw(RedrawEvent $event)
    {
        if ($this->nextBlock) {
            if ($this->erase) {
                for ($y = $this->nextBlock->getYPosition(); $y < $this->nextBlock->getYPosition() + 4; $y++) {
                    for ($x = $this->nextBlock->getXPosition(); $x < $this->nextBlock->getXPosition() + 4; $x++) {
                        $xPosition = $x * $this->horizontalScale;
                        for ($i = 0; $i < $this->horizontalScale; $i++) {
                            $event->getOutput()->putNextValue($xPosition + $i, $y, ' ');
                        }
                    }
                }
            }

            $this->nextBlock->draw($event->getOutput(), $this->horizontalScale);
        }
    }

    /**
     * @return AbstractBlock
     */
    public function getNextBlock()
    {
        $block = $this->nextBlock;
        $this->nextBlock = null;
        $this->erase = true;

        return $block;
    }
}
