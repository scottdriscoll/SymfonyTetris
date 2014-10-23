<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Entity\GameScore;
use SD\TetrisBundle\Entity\GameScoreRepository;
use SD\TetrisBundle\Event\ScoreTalliedEvent;

/**
 * @DI\Service("game.leaderboard_manager")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class LeaderBoardManager
{
    /**
     * @var GameScoreRepository
     */
    private $gameScoreRepository;

    /**
     * @DI\InjectParams({
     *     "gameScoreRepository" = @DI\Inject("gamescore_repository")
     * })
     *
     * @param GameScoreRepository $gameScoreRepository
     */
    public function __construct(GameScoreRepository $gameScoreRepository)
    {
        $this->gameScoreRepository = $gameScoreRepository;
    }

    /**
     * @DI\Observe(Events::SCORE_TALLIED, priority = 0)
     *
     * @param ScoreTalliedEvent $event
     */
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
