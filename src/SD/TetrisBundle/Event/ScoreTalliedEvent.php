<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ScoreTalliedEvent extends Event
{
    /**
     * @var int
     */
    private $score;

    /**
     * @var string|null
     */
    private $opponentName;

    /**
     * @var int|null
     */
    private $opponentScore;

    /**
     * @param int $score
     * @param string $opponentName
     * @param int $opponentScore
     */
    public function __construct($score, $opponentName, $opponentScore)
    {
        $this->score = $score;
        $this->opponentName = $opponentName;
        $this->opponentScore = $opponentScore;
    }

    /**
     * @return null|string
     */
    public function getOpponentName()
    {
        return $this->opponentName;
    }

    /**
     * @return int|null
     */
    public function getOpponentScore()
    {
        return $this->opponentScore;
    }

    /**
     * @return int
     */
    public function getScore()
    {
        return $this->score;
    }
}
