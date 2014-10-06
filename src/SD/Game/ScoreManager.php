<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\RedrawEvent;
use SD\TetrisBundle\Event\LinesClearedEvent;
use SD\TetrisBundle\Event\StageClearedEvent;

/**
 * @DI\Service
 *
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
     * @var int
     */
    private $score = 0;

    /**
     * @var int
     */
    private $stage = 0;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "width" = @DI\Inject("%board_width%"),
     *     "horizontalScale" = @DI\Inject("%horizontal_scale%")
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param int $width
     * @param int $horizontalScale
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $width, $horizontalScale)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->width = $width;
        $this->horizontalScale = $horizontalScale;
    }

    /**
     * @DI\Observe(Events::REDRAW, priority = 0)
     *
     * @param RedrawEvent $event
     */
    public function draw(RedrawEvent $event)
    {
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 8, ['Score']);
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 9, ["{$this->score}"]);
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 11, ['Stage']);
        $event->getOutput()->putArrayOfValues($this->width * $this->horizontalScale + 5, 12, ["{$this->stage}"]);
    }

    /**
     * @DI\Observe(Events::LINES_CLEARED, priority = 0)
     *
     * @param LinesClearedEvent $event
     */
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
            $this->eventDispatcher->dispatch(Events::STAGE_CLEARED, new StageClearedEvent());
        }
    }
}
