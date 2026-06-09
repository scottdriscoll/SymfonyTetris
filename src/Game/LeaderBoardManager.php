<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use App\Entity\GameScore;
use App\Event\ScoreTalliedEvent;
use App\Repository\GameScoreRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class LeaderBoardManager
{
    /**
     * @var GameScoreRepository
     */
    private $gameScoreRepository;

    public function __construct(GameScoreRepository $gameScoreRepository)
    {
        $this->gameScoreRepository = $gameScoreRepository;
    }

    /**
     * @param ScoreTalliedEvent $event
     */
    #[AsEventListener]
    public function logGamePlayed(ScoreTalliedEvent $event)
    {
        try {
            $gameScore = new GameScore();
            $gameScore->setTimePlayed(new \DateTime());
            $gameScore->setScore($event->getScore());
            $gameScore->setOpponentName($event->getOpponentName());
            $gameScore->setOpponentScore($event->getOpponentScore());
            $this->gameScoreRepository->store($gameScore);
        } catch (\Exception $e) {
            // User does not have the database installed
        }
    }

    /**
     * @return array
     */
    public function getLeaderBoard()
    {
        try {
            $leaderboard = $this->gameScoreRepository->findBy([], ['score' => 'DESC'], 15);
        } catch (\Exception $e) {
            $leaderboard = [];
        }

        return $leaderboard;
    }
}
