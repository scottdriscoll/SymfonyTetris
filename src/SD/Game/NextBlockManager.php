<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\TetrisBundle\Event\RedrawEvent;
use SD\Game\Block\BlockFactory;
use SD\Game\Block\AbstractBlock;

/**
 * @DI\Service("game.next_block_manager")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class NextBlockManager
{
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

    /**
     * @DI\InjectParams({
     *     "blockFactory" = @DI\Inject("game.block_factory"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "horizontalScale" = @DI\Inject("%horizontal_scale%")
     * })
     *
     * @param BlockFactory $blockFactory
     * @param int $width
     * @param int $horizontalScale
     */
    public function __construct(BlockFactory $blockFactory, $width, $horizontalScale)
    {
        $this->blockFactory = $blockFactory;
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
        if (null === $this->nextBlock) {
            $this->nextBlock = $this->blockFactory->getRandomBlock();
            $this->nextBlock->setXPosition($this->width + 3);
            $this->nextBlock->setYPosition(1);
        }
    }

    /**
     * @DI\Observe(Events::REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
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
