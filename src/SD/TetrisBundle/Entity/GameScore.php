<?php

namespace SD\TetrisBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GameScore
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 *
 * @ORM\Table(name="game_score",indexes={@ORM\Index(name="time_played_idx", columns={"time_played"})})
 * @ORM\Entity(repositoryClass="SD\TetrisBundle\Entity\GameScoreRepository")
 */
class GameScore
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="time_played", type="datetimetz")
     */
    private $timePlayed;

    /**
     * @var integer
     *
     * @ORM\Column(name="score", type="integer")
     */
    private $score;

    /**
     * @var string
     *
     * @ORM\Column(name="opponent_name", type="string", length=255, nullable=true)
     */
    private $opponentName;

    /**
     * @var integer
     *
     * @ORM\Column(name="opponent_score", type="integer", nullable=true)
     */
    private $opponentScore;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set timePlayed
     *
     * @param \DateTime $timePlayed
     * @return GameScore
     */
    public function setTimePlayed($timePlayed)
    {
        $this->timePlayed = $timePlayed;

        return $this;
    }

    /**
     * Get timePlayed
     *
     * @return \DateTime 
     */
    public function getTimePlayed()
    {
        return $this->timePlayed;
    }

    /**
     * Set sore
     *
     * @param integer $score
     * @return GameScore
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return integer 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set opponentName
     *
     * @param string $opponentName
     * @return GameScore
     */
    public function setOpponentName($opponentName)
    {
        $this->opponentName = $opponentName;

        return $this;
    }

    /**
     * Get opponentName
     *
     * @return string 
     */
    public function getOpponentName()
    {
        return $this->opponentName;
    }

    /**
     * Set opponentScore
     *
     * @param integer $opponentScore
     * @return GameScore
     */
    public function setOpponentScore($opponentScore)
    {
        $this->opponentScore = $opponentScore;

        return $this;
    }

    /**
     * Get opponentScore
     *
     * @return integer 
     */
    public function getOpponentScore()
    {
        return $this->opponentScore;
    }
}
