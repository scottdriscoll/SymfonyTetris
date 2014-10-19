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
     * @var int
     */
    private $score;

    /**
     * @var int
     */
    private $stage;

    /**
     * @param array $board
     * @param AbstractBlock $block
     * @param int $score
     * @param int $stage
     */
    public function __construct(array $board, AbstractBlock $block, $score, $stage)
    {
        $this->board = $board;
        $this->activeBlock = $block;
        $this->stage = $stage;
        $this->score = $score;
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

    /**
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @return int
     */
    public function getStage()
    {
        return $this->stage;
    }
}
