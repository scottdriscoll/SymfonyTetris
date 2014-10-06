<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\TetrisBundle\Event\RedrawEvent;
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
     * @var AbstractBlock
     */
    private $activeBlock;

    /**
     * @var AbstractBlock
     */
    private $nextBlock;

    /**
     * @var int
     */
    private $lastUpdate = 0;

    /**
     * @var float
     */
    private $fallDelay = 0.125;

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
     */
    public function initialize(OutputHelper $output)
    {
        $this->output = $output;
        $this->buffer->initialize($this->width * $this->horizontalScale + 20, $this->height + 5);

        for ($h = 0; $h < $this->height; $h++) {
            for ($w = 0; $w < $this->width; $w++) {
                $this->board[$h][$w] = new GameBoardUnit();
            }
        }
    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 255)
     *
     * @param HeartbeatEvent $event
     */
    public function updateBoard(HeartbeatEvent $event)
    {

    }

    /**
     * @DI\Observe(Events::HEARTBEAT, priority = 254)
     *
     * @param HeartbeatEvent $event
     *
     * @throws \Exception
     */
    public function drawBoard(HeartbeatEvent $event)
    {
        if (null === $this->output) {
            throw new \Exception('OutputHelper not initialized');
        }

        $this->output->clear();
        $this->buffer->clearScreen();

        $scaledWidth = $this->width * $this->horizontalScale;

        // Draw board
        for ($x = 0; $x < $scaledWidth + 2; $x++) {
            $this->buffer->putNextValue($x, 0, '-');
        }

        for ($y = 1; $y < $this->height; $y++) {
            $this->buffer->putNextValue(0, $y, '|');
            $this->buffer->putNextValue($scaledWidth + 1, $y, '|');
        }

        for ($x = 0; $x < $scaledWidth + 2; $x++) {
            $this->buffer->putNextValue($x, $this->height, '-');
        }

        for ($y = 0; $y < $this->height - 1; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $color = $this->board[$y][$x]->getColor();
                for ($i = 0; $i < $this->horizontalScale; $i++) {
                    $this->buffer->putNextValue($x * $this->horizontalScale + $i + 1, $y + 1, ' ', null, $color);
                }
            }
        }

        $this->eventDispatcher->dispatch(Events::REDRAW, new RedrawEvent($this->buffer));

        $this->buffer->paintChanges($this->output);
        $this->buffer->nextFrame();
        $this->output->dump();
    }

    /**
     * @param AbstractBlock $block
     *
     * @return bool
     */
    public function doesBlockFix(AbstractBlock $block)
    {
        // Check borders
        if ($block->getXPosition() <= 0 || ($block->getXPosition() + $block->getLength() - 1) > $this->width) {
            return false;
        }

        return true;
    }
}
