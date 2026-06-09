<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Event\RedrawEvent;
use App\Event\BlockReachedBottomEvent;
use App\Event\LinesClearedEvent;
use App\Event\BlockMovedEvent;
use App\Event\NextBlockReadyEvent;
use App\Event\PlayerConnectedEvent;
use App\Event\MultiplayerBoardUpdateEvent;
use App\Event\AddLinesEvent;
use App\Game\Block\AbstractBlock;
use SD\ConsoleHelper\ScreenBuffer;
use SD\ConsoleHelper\OutputHelper;

/**
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
     * @param BlockMovedEvent $event
     */
    #[AsEventListener]
    public function screenDirty(BlockMovedEvent $event)
    {
        $this->drawBoard();
    }

    /**
     * @param NextBlockReadyEvent $event
     */
    #[AsEventListener]
    public function nextBlockReady(NextBlockReadyEvent $event)
    {
        $this->drawBoard();
    }

    /**
     * @param BlockReachedBottomEvent $event
     */
    #[AsEventListener]
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
     * @param MultiplayerBoardUpdateEvent $event
     */
    #[AsEventListener]
    public function peerBoardUpdate(MultiplayerBoardUpdateEvent $event)
    {
        $this->peerBoard = $event->getMessage()->getBoard();
        $this->peerBlock = $event->getMessage()->getActiveBlock();
        $x = $this->peerBlock->getXPosition() + self::PEER_BOARD_OFFSET;
        $this->peerBlock->setXPosition($x);
    }

    /**
     * @param PlayerConnectedEvent $event
     */
    #[AsEventListener]
    public function peerConnected(PlayerConnectedEvent $event)
    {
        $this->peerName = $event->getPeerName();
    }

    /**
     * Add random lines to the bottom of the board, removing lines from the top
     *
     * @param AddLinesEvent $event
     */
    #[AsEventListener]
    public function addLines(AddLinesEvent $event)
    {
        $lines = $event->getLines();
        $newBoard = [];

        for ($h = 1; $h <= $this->height - $lines; $h++) {
            for ($w = 1; $w <= $this->width; $w++) {
                $newBoard[$h][$w] = $this->board[$h + $lines][$w];
            }
        }

        // Add random lines
        for (; $h <= $this->height; $h++) {
            for ($w = 1; $w <= $this->width; $w++) {
                $newBoard[$h][$w] = new GameBoardUnit();

                // 50% chance to add a block
                if (rand(1, 100) < 50) {
                    $newBoard[$h][$w]->setOccupied(AbstractBlock::getRandomColor());
                }
            }
        }

        $this->board = $newBoard;
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

        $this->eventDispatcher->dispatch(new LinesClearedEvent(count($linesCleared)));
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

        $this->eventDispatcher->dispatch(new RedrawEvent($this->buffer));

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
