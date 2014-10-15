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
     * @var string
     */
    private $name;

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
        $this->output = $output;
        $this->buffer->initialize(2 * $this->width * $this->horizontalScale + 30, $this->height + 5);
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

        $scaledWidth = $this->width * $this->horizontalScale;

        // Draw board
        for ($x = 0; $x < $scaledWidth + 2; $x++) {
            $this->buffer->putNextValue($x, 0, '-');
        }

        for ($y = 1; $y < $this->height + 1; $y++) {
            $this->buffer->putNextValue(0, $y, '|');
            $this->buffer->putNextValue($scaledWidth + 1, $y, '|');
        }

        for ($x = 0; $x < $scaledWidth + 2; $x++) {
            $this->buffer->putNextValue($x, $this->height + 1, '-');
        }

        for ($y = 1; $y <= $this->height; $y++) {
            for ($x = 1; $x <= $this->width; $x++) {
                $color = $this->board[$y][$x]->getColor();
                for ($i = 0; $i < $this->horizontalScale; $i++) {
                    $this->buffer->putNextValue($x * $this->horizontalScale + $i - 1, $y, ' ', null, $color);
                }
            }
        }

        if (null !== $this->name) {
            $this->buffer->putArrayOfValues(0, $this->height + 2, [$this->name]);
        }

        $this->eventDispatcher->dispatch(Events::REDRAW, new RedrawEvent($this->buffer));

        $this->buffer->paintChanges($this->output);
        $this->buffer->nextFrame();
        $this->output->dump();
    }
}
