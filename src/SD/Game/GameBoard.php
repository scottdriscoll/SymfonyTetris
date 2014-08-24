<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\Game\Block\AbstractBlock;
use SD\ConsoleHelper\ScreenBuffer;
use SD\ConsoleHelper\OutputHelper;
use SD\Game\Block\BlockFactory;

/**
 * @DI\Service("game.game_board")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameBoard
{
    /**
     * Scales up the size of the board, keeping the same number of block spaces
     */
    const HORIZONTAL_SCALE = 3;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $height;

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var BlockFactory
     */
    private $blockFactory;

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
     *     "blockFactory" = @DI\Inject("game.block_factory"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "height" = @DI\Inject("%board_height%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ScreenBuffer $buffer;
     * @param BlockFactory $blockFactory
     * @param int $width
     * @param int $height
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ScreenBuffer $buffer, BlockFactory $blockFactory, $width, $height)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->buffer = $buffer;
        $this->blockFactory = $blockFactory;
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @param OutputHelper $output
     */
    public function initialize(OutputHelper $output)
    {
        $this->output = $output;
        $this->buffer->initialize($this->width * self::HORIZONTAL_SCALE + 20, $this->height + 5);

        for ($h = 0; $h < $this->height; $h++) {
            for ($w = 0; $w < $this->width; $w++) {
                $this->board[$h][$w] = new GameBoardUnit();
            }
        }

        $this->activeBlock = $this->blockFactory->getRandomBlock();
        $this->nextBlock = $this->blockFactory->getRandomBlock();
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
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
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

        $scaledWidth = $this->width * self::HORIZONTAL_SCALE;

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

        // Draw next piece

        // Draw current piece

        $this->buffer->paintChanges($this->output);
        $this->buffer->nextFrame();
        $this->output->dump();
    }
}
