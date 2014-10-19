<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameOverEvent extends Event
{
    /**
     * @var bool
     */
    private $playerWins;

    /**
     * @param $playerWins
     */
    public function __construct($playerWins)
    {
        $this->playerWins = $playerWins;
    }

    /**
     * @return bool
     */
    public function getPlayerWins()
    {
        return $this->playerWins;
    }
}
