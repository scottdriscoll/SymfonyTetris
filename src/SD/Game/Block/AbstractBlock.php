<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
abstract class AbstractBlock
{
    /**
     * @var int
     */
    protected  $xPosition;

    /**
     * @var int
     */
    protected  $yPosition;

    /**
     * @var int
     */
    protected $currentIndex = 0;

    /**
     * @var array
     */
    protected $block = [];

    /**
     * @param int $xPosition
     */
    public function setXPosition($xPosition)
    {
        $this->xPosition = $xPosition;
    }

    /**
     * @return int
     */
    public function getXPosition()
    {
        return $this->xPosition;
    }

    /**
     * @param int $yPosition
     */
    public function setYPosition($yPosition)
    {
        $this->yPosition = $yPosition;
    }

    /**
     * @return int
     */
    public function getYPosition()
    {
        return $this->yPosition;
    }

    public function rotate()
    {
        $this->currentIndex++;
        if ($this->currentIndex == count($this->block)) {
            $this->currentIndex = 0;
        }
    }

    /**
     * @param ScreenBuffer $buffer
     */
    abstract public function draw(ScreenBuffer $buffer);
}
