<?php

namespace App\Tui;

use App\Event\GameOverEvent;
use App\Event\HeartbeatEvent;
use App\Event\KeyboardDownEvent;
use App\Event\KeyboardLeftEvent;
use App\Event\KeyboardRightEvent;
use App\Event\KeyboardRotateEvent;
use App\Event\KeyboardToggleLandingPreviewEvent;
use App\Event\UserClosedEvent;
use App\Game\GameBoard;
use App\Game\KeyboardListener;
use App\Tui\Widget\TetrisDashboardWidget;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Tui\Event\InputEvent;
use Symfony\Component\Tui\Input\Key;
use Symfony\Component\Tui\Input\KeyParser;
use Symfony\Component\Tui\Tui;

final readonly class TetrisTuiRunner
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private GameBoard $gameBoard,
        private KeyboardListener $keyboardListener,
        private GameViewStateFactory $stateFactory,
    ) {
    }

    public function run(): void
    {
        $this->gameBoard->initialize();
        $this->keyboardListener->setEnabled(false);

        $tui = new Tui();
        $dashboard = new TetrisDashboardWidget();
        $dashboard->setState($this->stateFactory->create());
        $tui->add($dashboard);

        $gameOver = false;
        $keyParser = new KeyParser();

        $gameOverListener = function (GameOverEvent $event) use (&$gameOver): void {
            $gameOver = true;
        };
        $this->eventDispatcher->addListener(GameOverEvent::class, $gameOverListener, -255);

        $tui->addListener(function (InputEvent $event) use ($keyParser, $tui, $dashboard, &$gameOver): void {
            $key = $keyParser->parse($event->getData())['key'] ?? null;
            $gameEvent = match ($key) {
                Key::LEFT => new KeyboardLeftEvent(),
                Key::RIGHT => new KeyboardRightEvent(),
                Key::DOWN => new KeyboardDownEvent(),
                Key::SPACE => new KeyboardRotateEvent(),
                'h', 'H' => new KeyboardToggleLandingPreviewEvent(),
                Key::ESCAPE, 'q', Key::ctrl('c') => new UserClosedEvent(),
                default => null,
            };

            if (null === $gameEvent) {
                return;
            }

            $this->eventDispatcher->dispatch($gameEvent);

            if ($gameEvent instanceof UserClosedEvent) {
                $gameOver = true;
                $tui->stop();
            }

            $event->stopPropagation();
            if ($dashboard->setState($this->stateFactory->create($gameOver))) {
                $tui->requestRender();
            }
        });

        $tui->onTick(function () use ($tui, $dashboard, &$gameOver): bool {
            $this->eventDispatcher->dispatch(new HeartbeatEvent(microtime(true)));

            if ($dashboard->setState($this->stateFactory->create($gameOver))) {
                $tui->requestRender();
            }

            if ($gameOver) {
                $tui->stop();
            }

            return !$gameOver;
        });

        try {
            $tui->run();
        } finally {
            $this->keyboardListener->setEnabled(true);
            $this->eventDispatcher->removeListener(GameOverEvent::class, $gameOverListener);
        }
    }
}
