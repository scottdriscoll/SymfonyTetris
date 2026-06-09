<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Game;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use App\Event\HeartbeatEvent;
use App\Event\KeyboardLeftEvent;
use App\Event\KeyboardRightEvent;
use App\Event\KeyboardDownEvent;
use App\Event\KeyboardRotateEvent;
use SD\ConsoleHelper\Keyboard as KeyboardHelper;

/**
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

    private bool $enabled = true;

    public function __construct(EventDispatcherInterface $eventDispatcher, KeyboardHelper $keyboardHelper)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->keyboardHelper = $keyboardHelper;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @param HeartbeatEvent $event
     */
    #[AsEventListener]
    public function processKeyboardEvents(HeartbeatEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        if (($key = $this->keyboardHelper->readKey()) !== null) {
            switch ($key) {
                case KeyboardHelper::LEFT_ARROW:
                    $this->eventDispatcher->dispatch(new KeyboardLeftEvent());
                    break;

                case KeyboardHelper::RIGHT_ARROW:
                    $this->eventDispatcher->dispatch(new KeyboardRightEvent());
                    break;

                case KeyboardHelper::DOWN_ARROW:
                    $this->eventDispatcher->dispatch(new KeyboardDownEvent());
                    break;

                case self::KEY_ROTATE:
                    $this->eventDispatcher->dispatch(new KeyboardRotateEvent());
                    break;
            }
        }
    }
}
