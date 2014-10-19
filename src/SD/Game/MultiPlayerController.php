<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use SD\Game\Sockets\Message\AddLinesMessage;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\Game\Sockets\Udp2p;
use SD\Game\ScoreManager;
use SD\Game\Sockets\Message\BoardUpdateMessage;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\TetrisBundle\Event\GameOverEvent;
use SD\Game\GameBoard;
use SD\Game\Sockets\Message\GameOverMessage;
use SD\TetrisBundle\Event\PeerLoseEvent;
use SD\TetrisBundle\Event\LinesClearedEvent;
use SD\TetrisBundle\Event\UserClosedEvent;

/**
 * @DI\Service("game.multiplayer_controller")
 *
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

    /**
     * @DI\InjectParams({
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher"),
     *     "udp2p"              = @DI\Inject("game.udp2p"),
     *     "activeBlockManager" = @DI\Inject("game.active_block_manager"),
     *     "scoreManager"       = @DI\Inject("game.score_manager"),
     *     "gameBoard"          = @DI\Inject("game.game_board")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Udp2p $udp2p
     * @param ActiveBlockManager $activeBlockManager
     * @param ScoreManager $scoreManager
     * @param GameBoard $gameBoard
     */
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
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
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
     * @DI\Observe(Events::GAME_OVER, priority = 255)
     *
     * @param GameOverEvent $event
     */
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
     * @DI\Observe(Events::MESSAGE_PEER_LOSE, priority = 0)
     *
     * @param PeerLoseEvent $event
     */
    public function peerLose(PeerLoseEvent $event)
    {
        $this->playerWins = true;
    }

    /**
     * @DI\Observe(Events::LINES_CLEARED, priority = 0)
     *
     * @param LinesClearedEvent $event
     */
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
     * @DI\Observe(Events::USER_CLOSED, priority = 0)
     *
     * @param UserClosedEvent $event
     */
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
