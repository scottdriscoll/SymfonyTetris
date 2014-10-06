<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use JMS\DiExtraBundle\Annotation as DI;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\HeartbeatEvent;
use SD\TetrisBundle\Event\KeyboardLeftEvent;
use SD\TetrisBundle\Event\KeyboardRightEvent;
use SD\TetrisBundle\Event\KeyboardDownEvent;
use SD\TetrisBundle\Event\KeyboardRotateEvent;
use SD\ConsoleHelper\Keyboard as KeyboardHelper;

/**
 * @DI\Service("game.keyboard_listener")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class KeyboardListener
{
    const KEY_ROTATE = ' ';

    /**
     * @var EventDispatcherInterface $eventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var KeyboardHelper
     */
    private $keyboardHelper;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "keyboardHelper" = @DI\Inject("keyboard_helper"),
     * })
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param KeyboardHelper $keyboardHelper
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, KeyboardHelper $keyboardHelper)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->keyboardHelper = $keyboardHelper;
    }

        /**
     * @DI\Observe(Events::HEARTBEAT, priority = 0)
     *
     * @param HeartbeatEvent $event
     */
    public function processKeyboardEvents(HeartbeatEvent $event)
    {
        if (($key = $this->keyboardHelper->readKey()) !== null) {
            switch ($key) {
                case KeyboardHelper::LEFT_ARROW:
                    $this->eventDispatcher->dispatch(Events::KEYBOARD_LEFT, new KeyboardLeftEvent());
                    break;

                case KeyboardHelper::RIGHT_ARROW:
                    $this->eventDispatcher->dispatch(Events::KEYBOARD_RIGHT, new KeyboardRightEvent());
                    break;

                case KeyboardHelper::DOWN_ARROW:
                    $this->eventDispatcher->dispatch(Events::KEYBOARD_DOWN, new KeyboardDownEvent());
                    break;

                case self::KEY_ROTATE:
                    $this->eventDispatcher->dispatch(Events::KEYBOARD_ROTATE, new KeyboardRotateEvent());
                    break;
            }
        }
    }
}
