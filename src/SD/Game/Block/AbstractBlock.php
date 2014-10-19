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
     * @var string
     */
    protected $color;

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
     * @return int
     */
    public function getLength()
    {
        return $this->block[$this->currentIndex]['length'];
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->block[$this->currentIndex]['height'];
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
     * @param int $horizontalScale
     */
    public function draw(ScreenBuffer $buffer, $horizontalScale)
    {
        $shapes = $this->block[$this->currentIndex]['shapes'];
        $y = $this->yPosition;

        foreach ($shapes as $row) {
            for ($i = 0; $i < strlen($row); $i++) {
                if (substr($row, $i, 1) === '.') {
                    for ($j = 0; $j < $horizontalScale; $j++) {
                        $x = ($this->xPosition + $i) * $horizontalScale + $j - 1;
                        $buffer->putNextValue($x, $y, ' ', null, $this->color);
                    }
                }
            }

            $y++;
        }
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @return array
     */
    public function getVisibleCoordinates()
    {
        $coordinates = [];
        $yOffset = 0;

        foreach ($this->block[$this->currentIndex]['shapes'] as $row) {
            for ($xOffset = 0; $xOffset < strlen($row); $xOffset++) {
                if (substr($row, $xOffset, 1) === '.') {
                    $coordinates[] = ['x' => $this->xPosition + $xOffset, 'y' => $this->yPosition + $yOffset];
                }
            }

            $yOffset++;
        }

        return $coordinates;
    }

    public static function getRandomColor()
    {
        static $colors = ['red', 'magenta', 'yellow', 'cyan', 'blue', 'green'];

        return $colors[rand(0, 5)];
    }
}
