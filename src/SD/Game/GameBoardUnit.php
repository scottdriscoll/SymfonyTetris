<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameBoardUnit
{
    /**
     * @var bool
     */
    private $occupied = false;

    /**
     * @var string
     */
    private $color;

    public function setEmpty()
    {
        $this->occupied = false;
    }

    /**
     * @param string $color
     */
    public function setOccupied($color)
    {
        $this->occupied = true;
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->occupied ? $this->color : 'black';
    }
}
