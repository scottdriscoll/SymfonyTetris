<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Sockets\Message;

use SD\Game\Block\AbstractBlock;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class BoardUpdateMessage extends AbstractMessage
{
    /**
     * @var array
     */
    private $board;

    /**
     * @var AbstractBlock
     */
    private $activeBlock;

    /**
     * @param array $board
     * @param AbstractBlock $block
     */
    public function __construct(array $board, AbstractBlock $block)
    {
        $this->board = $board;
        $this->activeBlock = $block;
    }

    /**
     * @return AbstractBlock
     */
    public function getActiveBlock()
    {
        return $this->activeBlock;
    }

    /**
     * @return array
     */
    public function getBoard()
    {
        return $this->board;
    }
}
