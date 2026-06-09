<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Event\RedrawEvent;
use App\Event\LinesClearedEvent;
use App\Event\StageClearedEvent;
use App\Event\PlayerConnectedEvent;
use App\Event\MultiplayerBoardUpdateEvent;
use App\Event\GameOverEvent;
use App\Event\ScoreTalliedEvent;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ScoreManager
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $horizontalScale;

    /**
     * @var string
     */
    private $playerName;

    /**
     * @var int
     */
    private $score = 0;

    /**
     * @var int
     */
    private $stage = 0;

    /**
     * @var string
     */
    private $peerName;

    /**
     * @var int
     */
    private $peerScore = 0;

    /**
     * @var int
     */
    private $peerStage = 0;

    public function __construct(EventDispatcherInterface $eventDispatcher, $width, $horizontalScale)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->width = $width;
        $this->horizontalScale = $horizontalScale;
    }

    /**
     * @param RedrawEvent $event
     */
    #[AsEventListener]
    public function draw(RedrawEvent $event)
    {
        if (!empty($this->playerName)) {
            $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 5, [substr($this->playerName, 0, 14)], 'green');
        }
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 6, ['Score']);
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 7, ["{$this->score}"]);
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 9, ['Stage']);
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 10, ["{$this->stage}"]);

        if (!empty($this->peerName)) {
            $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 3, 11, ['----------------']);
            $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 12, [substr($this->peerName, 0, 14)], 'red');
            $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 13, ['Score']);
            $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 14, ["{$this->peerScore}"]);
            $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 16, ['Stage']);
            $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 17, ["{$this->peerStage}"]);
        }
    }

    /**
     * @param LinesClearedEvent $event
     */
    #[AsEventListener]
    public function score(LinesClearedEvent $event)
    {
        switch ($event->getLinesClearedCount()) {
            case 1:
                $this->score += 100;
                break;
            case 2:
                $this->score += 250;
                break;
            case 3:
                $this->score += 500;
                break;
            case 4:
                $this->score += 1000;
                break;
        }

        $stage = floor($this->score / 2000);
        if ($stage > $this->stage) {
            $this->stage = $stage;
            $this->eventDispatcher->dispatch(new StageClearedEvent());
        }
    }

    /**
     * @param PlayerConnectedEvent $event
     */
    #[AsEventListener]
    public function peerConnected(PlayerConnectedEvent $event)
    {
        $this->playerName = $event->getName();
        $this->peerName = $event->getPeerName();
    }

    /**
     * @param MultiplayerBoardUpdateEvent $event
     */
    #[AsEventListener]
    public function peerBoardUpdate(MultiplayerBoardUpdateEvent $event)
    {
        $this->peerScore = $event->getMessage()->getScore();
        $this->peerStage = $event->getMessage()->getStage();
    }

    /**
     * @param GameOverEvent $event
     */
    #[AsEventListener]
    public function gameOver(GameOverEvent $event)
    {
        $this->eventDispatcher->dispatch(new ScoreTalliedEvent($this->score, $this->peerName, $this->peerScore));
    }

    /**
     * @return int
     */
    public function getPlayerScore()
    {
        return $this->score;
    }

    /**
     * @return int
     */
    public function getPlayerStage()
    {
        return $this->stage;
    }
}
