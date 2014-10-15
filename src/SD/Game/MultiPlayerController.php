<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

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
     * @var int
     */
    private $lastUpdate = 0;

    /**
     * @var BoardUpdateMessage
     */
    private $peerBoardMessage;

    /**
     * @var bool
     */
    private $peerBoardDirty = false;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $horizontalScale;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher"),
     *     "udp2p"              = @DI\Inject("game.udp2p"),
     *     "activeBlockManager" = @DI\Inject("game.active_block_manager"),
     *     "gameBoard"          = @DI\Inject("game.game_board"),
     *     "width"              = @DI\Inject("%board_width%"),
     *     "height"             = @DI\Inject("%board_height%"),
     *     "horizontalScale"    = @DI\Inject("%horizontal_scale%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param Udp2p $udp2p
     * @param ActiveBlockManager $activeBlockManager
     * @param GameBoard $gameBoard
     * @param int $width
     * @param int $height
     * @param int $horizontalScale
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Udp2p $udp2p, ActiveBlockManager $activeBlockManager, GameBoard $gameBoard, $width, $height, $horizontalScale)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->udp2p = $udp2p;
        $this->activeBlockManager = $activeBlockManager;
        $this->gameBoard = $gameBoard;
        $this->width = $width;
        $this->height = $height;
        $this->horizontalScale = $horizontalScale;
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
            die("not connected");
            return;
        }

        if (1||$event->getTimestamp() >= self::BOARD_UPDATE_FREQUENCY + $this->lastUpdate) {
            $this->lastUpdate = $event->getTimestamp();

            $board = $this->gameBoard->getBoard();
            if (empty($board)) {
                die("no board");
                return;
            }

            $block = $this->activeBlockManager->getActiveBlock();
            if (empty($block)) {
                echo "empty block" . rand();
                return;
            }

            $this->udp2p->sendMessage(new BoardUpdateMessage($board, $block));
        }

    }

    /**
     * @DI\Observe(Events::REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
    public function drawPeerBoard(RedrawEvent $event)
    {
        if (!$this->peerBoardDirty || !$this->udp2p->isConnected()) {
            return;
        }

        $this->peerBoardDirty = false;
        $buffer = $event->getOutput();
        $board = $this->peerBoardMessage->getBoard();

        $xStart = $this->width * $this->horizontalScale + 10;
        $scaledWidth = $this->width * $this->horizontalScale;

        // Draw board
        for ($x = $xStart; $x < $scaledWidth + 2; $x++) {
            $buffer->putNextValue($x, 0, '-');
        }

        for ($y = 1; $y < $this->height + 1; $y++) {
            $buffer->putNextValue(0, $y, '|');
            $buffer->putNextValue($scaledWidth + 1, $y, '|');
        }

        for ($x = $xStart; $x < $scaledWidth + 2; $x++) {
            $buffer->putNextValue($x, $this->height + 1, '-');
        }

        for ($y = 1; $y <= $this->height; $y++) {
            for ($x = $xStart + 1; $x <= $this->width; $x++) {
                $color = $board[$y][$x]->getColor();
                for ($i = 0; $i < $this->horizontalScale; $i++) {
                    $buffer->putNextValue($x * $this->horizontalScale + $i - 1, $y, ' ', null, $color);
                }
            }
        }
    }

    /**
     * @DI\Observe(Events::MESSAGE_BOARD_UPDATE, priority = 0)
     *
     * @param MultiplayerBoardUpdateEvent $event
     */
    public function peerBoardUpdate(MultiplayerBoardUpdateEvent $event)
    {
        $this->peerBoardMessage = $event->getMessage();
        $this->peerBoardDirty = true;
    }
}
