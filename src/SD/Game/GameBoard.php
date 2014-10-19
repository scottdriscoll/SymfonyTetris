<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\RedrawEvent;
use SD\TetrisBundle\Event\BlockReachedBottomEvent;
use SD\TetrisBundle\Event\LinesClearedEvent;
use SD\TetrisBundle\Event\BlockMovedEvent;
use SD\TetrisBundle\Event\NextBlockReadyEvent;
use SD\TetrisBundle\Event\PlayerConnectedEvent;
use SD\TetrisBundle\Event\MultiplayerBoardUpdateEvent;
use SD\Game\Block\AbstractBlock;
use SD\ConsoleHelper\ScreenBuffer;
use SD\ConsoleHelper\OutputHelper;

/**
 * @DI\Service("game.game_board")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameBoard
{
    const PEER_BOARD_OFFSET = 20;
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
     * @var EventDispatcherInterface $eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var ScreenBuffer
     */
    private $buffer;

    /**
     * @var OutputHelper
     */
    private $output;

    /**
     * @var array
     */
    private $board = [];

    /**
     * Multiplayer's board
     *
     * @var array
     */
    private $peerBoard = [];

    /**
     * @var AbstractBlock
     */
    private $peerBlock;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $peerName;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "buffer" = @DI\Inject("screen_buffer"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "height" = @DI\Inject("%board_height%"),
     *     "horizontalScale" = @DI\Inject("%horizontal_scale%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ScreenBuffer $buffer;
     * @param int $width
     * @param int $height
     * @param int $horizontalScale
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ScreenBuffer $buffer, $width, $height, $horizontalScale)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->buffer = $buffer;
        $this->width = $width;
        $this->height = $height;
        $this->horizontalScale = $horizontalScale;
    }

    /**
     * @param OutputHelper $output
     * @param string $name
     */
    public function initialize(OutputHelper $output, $name = null)
    {
        $multiplier = null === $name ? 1 : 2;
        $this->output = $output;
        $this->buffer->initialize($multiplier * $this->width * $this->horizontalScale + 30, $this->height + 5);
        $this->name = $name;

        for ($h = 1; $h <= $this->height; $h++) {
            for ($w = 1; $w <= $this->width; $w++) {
                $this->board[$h][$w] = new GameBoardUnit();
            }
        }
    }

    /**
     * @DI\Observe(Events::BLOCK_MOVED, priority = 0)
     *
     * @param BlockMovedEvent $event
     */
    public function screenDirty(BlockMovedEvent $event)
    {
        $this->drawBoard();
    }

    /**
     * @DI\Observe(Events::NEXT_BLOCK_READY, priority = 0)
     *
     * @param NextBlockReadyEvent $event
     */
    public function nextBlockReady(NextBlockReadyEvent $event)
    {
        $this->drawBoard();
    }

    /**
     * @DI\Observe(Events::BLOCK_REACHED_BOTTOM, priority = 0)
     *
     * @param BlockReachedBottomEvent $event
     */
    public function blockReachedBottomEvent(BlockReachedBottomEvent $event)
    {
        foreach ($event->getBlock()->getVisibleCoordinates() as $coordinates) {
            $this->board[$coordinates['y']][$coordinates['x']]->setOccupied($event->getBlock()->getColor());
        }

        $this->testForCompletedLines();
    }

    /**
     * @param AbstractBlock $block
     *
     * @return bool
     */
    public function doesBlockFit(AbstractBlock $block)
    {
        // Check borders
        if ($block->getXPosition() <= 0 || ($block->getXPosition() + $block->getLength() - 1) > $this->width) {
            return false;
        }

        if ($block->getYPosition() + $block->getHeight() - 1 > $this->height) {
            return false;
        }

        // Check already placed blocks
        foreach ($block->getVisibleCoordinates() as $coordinates) {
            if ($this->board[$coordinates['y']][$coordinates['x']]->isOccupied()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @DI\Observe(Events::MESSAGE_BOARD_UPDATE, priority = 0)
     *
     * @param MultiplayerBoardUpdateEvent $event
     */
    public function peerBoardUpdate(MultiplayerBoardUpdateEvent $event)
    {
        $this->peerBoard = $event->getMessage()->getBoard();
        $this->peerBlock = $event->getMessage()->getActiveBlock();
        $x = $this->peerBlock->getXPosition() + self::PEER_BOARD_OFFSET;
        $this->peerBlock->setXPosition($x);
    }

    /**
     * @DI\Observe(Events::MESSAGE_PLAYER_CONNECTED, priority = 0)
     *
     * @param PlayerConnectedEvent $event
     */
    public function peerConnected(PlayerConnectedEvent $event)
    {
        $this->peerName = $event->getPeerName();
    }

    private function testForCompletedLines()
    {
        $linesCleared = [];

        for ($h = 1; $h <= $this->height; $h++) {
            for ($w = 1; $w <= $this->width; $w++) {
                if (!$this->board[$h][$w]->isOccupied()) {
                    continue 2;
                }
            }

            $linesCleared[] = $h;
        }

        if (empty($linesCleared)) {
            return;
        }

        $this->eventDispatcher->dispatch(Events::LINES_CLEARED, new LinesClearedEvent(count($linesCleared)));
        $this->removedCompletedLines($linesCleared);
    }

    /**
     * @param array $lines
     */
    private function removedCompletedLines(array $lines)
    {
        $newBoard = [];

        // Create the new rows, replacing those that were completed
        for ($h = 1; $h <= count($lines); $h++) {
            for ($w = 1; $w <= $this->width; $w++) {
                $newBoard[$h][$w] = new GameBoardUnit();
            }
        }

        // Grab the rows from the game board, ignoring the completed ones
        for ($h = 1, $y = count($lines) + 1; $h <= $this->height; $h++) {
            if (in_array($h, $lines)) {
                continue;
            }

            for ($w = 1; $w <= $this->width; $w++) {
                $newBoard[$y][$w] = $this->board[$h][$w];
            }
            $y++;
        }

        // Swap the board
        $this->board = $newBoard;
    }

    private function drawBoard()
    {
        $this->output->clear();
        $this->buffer->clearScreen();


        if (null !== $this->name) {
            $this->buffer->putArrayOfValues(0, $this->height + 2, [$this->name], 'green');
            $this->buffer->putArrayOfValues($this->width * $this->horizontalScale + self::PEER_BOARD_OFFSET, $this->height + 2, [$this->peerName], 'red');
        }

        $this->drawBoardArray($this->board, 0);
        if (!empty($this->peerBoard)) {
            $this->drawBoardArray($this->peerBoard, $this->width * $this->horizontalScale + self::PEER_BOARD_OFFSET);
            $this->peerBlock->draw($this->buffer, $this->horizontalScale);
        }

        $this->eventDispatcher->dispatch(Events::REDRAW, new RedrawEvent($this->buffer));

        $this->buffer->paintChanges($this->output);
        $this->buffer->nextFrame();
        $this->output->dump();
    }

    /**
     * @param array $board
     * @param int $xOffset
     */
    private function drawBoardArray(array $board, $xOffset)
    {
        $scaledWidth = $this->width * $this->horizontalScale;

        // Draw board
        for ($x = $xOffset; $x < $scaledWidth + $xOffset + 2; $x++) {
            $this->buffer->putNextValue($x, 0, '-');
        }

        for ($y = 1; $y < $this->height + 1; $y++) {
            $this->buffer->putNextValue($xOffset, $y, '|');
            $this->buffer->putNextValue($xOffset + $scaledWidth + 1, $y, '|');
        }

        for ($x = $xOffset; $x < $scaledWidth + $xOffset + 2; $x++) {
            $this->buffer->putNextValue($x, $this->height + 1, '-');
        }

        for ($y = 1; $y <= $this->height; $y++) {
            for ($x = 1; $x <= $this->width; $x++) {
                $color = $board[$y][$x]->getColor();
                for ($i = 0; $i < $this->horizontalScale; $i++) {
                    $this->buffer->putNextValue($x * $this->horizontalScale + $xOffset + $i - 1, $y, ' ', null, $color);
                }
            }
        }
    }
}
