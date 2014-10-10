<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;

use SD\Game\Block\AbstractBlock;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class BlockMovedEvent extends Event
{
    /**
     * @var AbstractBlock
     */
    private $block;

    /**
     * @param AbstractBlock $block
     */
    public function __construct(AbstractBlock $block)
    {
        $this->block = $block;
    }

    /**
     * @return AbstractBlock
     */
    public function getBlock()
    {
        return $this->block;
    }
}
