<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use App\Game\Sockets\Message\AddLinesMessage;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Game\Sockets\Udp2p;
use App\Game\ScoreManager;
use App\Game\Sockets\Message\BoardUpdateMessage;
use App\Event\HeartbeatEvent;
use App\Event\GameOverEvent;
use App\Game\GameBoard;
use App\Game\Sockets\Message\GameOverMessage;
use App\Event\PeerLoseEvent;
use App\Event\LinesClearedEvent;
use App\Event\UserClosedEvent;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class MultiPlayerController
{
    /**
     * Sends our board to the other player every 1.5 seconds
     */
    const BOARD_UPDATE_FREQUENCY = 1500;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Udp2p
     */
    private $udp2p;

    /**
     * @var ActiveBlockManager
     */
    private $activeBlockManager;

    /**
     * @var ScoreManager
     */
    private $scoreManager;

    /**
     * @var GameBoard
     */
    private $gameBoard;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var bool
     */
    private $playerWins = false;

    public function __construct(EventDispatcherInterface $eventDispatcher, Udp2p $udp2p, ActiveBlockManager $activeBlockManager, ScoreManager $scoreManager, GameBoard $gameBoard)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->udp2p = $udp2p;
        $this->activeBlockManager = $activeBlockManager;
        $this->scoreManager = $scoreManager;
        $this->gameBoard = $gameBoard;
    }

    /**
     * Sends a snapshot of the current board, along with the active block
     *
     * @param HeartbeatEvent $event
     */
    #[AsEventListener]
    public function sendBoardUpdate(HeartbeatEvent $event)
    {
        if (!$this->udp2p->isConnected()) {
            return;
        }

        if (null === $this->stopwatch || $this->stopwatch->getEvent('mp')->getDuration() >= self::BOARD_UPDATE_FREQUENCY) {
            $board = $this->gameBoard->getBoard();
            if (empty($board)) {
                return;
            }

            $block = $this->activeBlockManager->getActiveBlock();
            if (empty($block)) {
                return;
            }

            $this->stopwatch = new Stopwatch();
            $this->stopwatch->start('mp');

            $this->udp2p->sendMessage(new BoardUpdateMessage($board, $block, $this->scoreManager->getPlayerScore(), $this->scoreManager->getPlayerStage()));
        }
    }

    /**
     * @param GameOverEvent $event
     */
    #[AsEventListener(priority: 255)]
    public function gameOver(GameOverEvent $event)
    {
        if (!$this->udp2p->isConnected()) {
            return;
        }

        if ($event->getSource() == GameOverEvent::SOURCE_SELF) {
            $this->udp2p->sendMessage(new GameOverMessage());
        }
    }

    /**
     * @param PeerLoseEvent $event
     */
    #[AsEventListener]
    public function peerLose(PeerLoseEvent $event)
    {
        $this->playerWins = true;
    }

    /**
     * @param LinesClearedEvent $event
     */
    #[AsEventListener]
    public function linesCleared(LinesClearedEvent $event)
    {
        $linesCleared = $event->getLinesClearedCount();

        if ($linesCleared < 2) {
            return;
        }

        $message = new AddLinesMessage($linesCleared);
        $message->setCritical(true);

        $this->udp2p->sendMessage($message);
    }

    /**
     * @param UserClosedEvent $event
     */
    #[AsEventListener]
    public function userClosed(UserClosedEvent $event)
    {
        if (!$this->udp2p->isConnected()) {
            return;
        }

        $this->udp2p->sendMessage(new GameOverMessage());
    }

    /**
     * @return bool
     */
    public function didPlayerWin()
    {
        return $this->playerWins;
    }
}
