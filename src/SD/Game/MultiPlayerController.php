<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\Game\Sockets\Udp2p;
use SD\Game\Sockets\Message\BoardUpdateMessage;
use SD\TetrisBundle\Event\MultiplayerBoardUpdateEvent;
use SD\Game\GameBoard;
use SD\Game\ActiveBlockManager;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\TetrisBundle\Event\RedrawEvent;
use SD\TetrisBundle\Event\NextBlockReadyEvent;
use SD\Game\Block\BlockFactory;
use SD\Game\Block\AbstractBlock;

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
     * @var GameBoard
     */
    private $gameBoard;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher"),
     *     "udp2p"              = @DI\Inject("game.udp2p"),
     *     "activeBlockManager" = @DI\Inject("game.active_block_manager"),
     *     "gameBoard"          = @DI\Inject("game.game_board")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Udp2p $udp2p
     * @param ActiveBlockManager $activeBlockManager
     * @param GameBoard $gameBoard
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Udp2p $udp2p, ActiveBlockManager $activeBlockManager, GameBoard $gameBoard)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->udp2p = $udp2p;
        $this->activeBlockManager = $activeBlockManager;
        $this->gameBoard = $gameBoard;
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start('mp');
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

        if ($this->stopwatch->getEvent('mp')->getDuration() >= self::BOARD_UPDATE_FREQUENCY) {
            $this->stopwatch = new Stopwatch();
            $this->stopwatch->start('mp');

            $board = $this->gameBoard->getBoard();
            if (empty($board)) {
                return;
            }

            $block = $this->activeBlockManager->getActiveBlock();
            if (empty($block)) {
                return;
            }

            $this->udp2p->sendMessage(new BoardUpdateMessage($board, $block));
        }
    }
}
