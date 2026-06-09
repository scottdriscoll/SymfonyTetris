<?php

namespace App\Tui\Widget;

use App\Game\Block\AbstractBlock;
use App\Tui\GameViewState;
use Symfony\Component\Tui\Render\RenderContext;
use Symfony\Component\Tui\Widget\AbstractWidget;

final class TetrisDashboardWidget extends AbstractWidget
{
    private ?GameViewState $state = null;
    private ?string $lastSignature = null;

    public function setState(GameViewState $state): bool
    {
        if ($this->lastSignature === $state->signature) {
            return false;
        }

        $this->state = $state;
        $this->lastSignature = $state->signature;
        $this->invalidate();

        return true;
    }

    /**
     * @return string[]
     */
    public function render(RenderContext $context): array
    {
        if (null === $this->state) {
            return ['Loading Tetris...'];
        }

        $lines = [
            "\033[1mSymfony Tetris\033[0m",
            'Arrows move, space rotates, q quits',
            '',
        ];

        $boardLines = $this->renderBoard($this->state);
        $sideLines = $this->renderSidePanel($this->state);
        $lineCount = max(count($boardLines), count($sideLines));

        for ($i = 0; $i < $lineCount; $i++) {
            $boardLine = $boardLines[$i] ?? str_repeat(' ', $this->state->width * 2 + 2);
            $sideLine = $sideLines[$i] ?? '';
            $lines[] = $boardLine.'  '.$sideLine;
        }

        if ($this->state->gameOver) {
            $lines[] = '';
            $lines[] = "\033[1;31mGame over\033[0m";
        }

        return $lines;
    }

    /**
     * @return string[]
     */
    private function renderBoard(GameViewState $state): array
    {
        $colors = [];

        for ($y = 1; $y <= $state->height; $y++) {
            for ($x = 1; $x <= $state->width; $x++) {
                $colors[$y][$x] = $state->board[$y][$x]->getColor();
            }
        }

        if (null !== $state->activeBlock) {
            foreach ($state->activeBlock->getVisibleCoordinates() as $coordinate) {
                if ($coordinate['y'] >= 1 && $coordinate['y'] <= $state->height && $coordinate['x'] >= 1 && $coordinate['x'] <= $state->width) {
                    $colors[$coordinate['y']][$coordinate['x']] = $state->activeBlock->getColor();
                }
            }
        }

        $lines = ['+'.str_repeat('-', $state->width * 2).'+'];
        for ($y = 1; $y <= $state->height; $y++) {
            $line = '|';
            for ($x = 1; $x <= $state->width; $x++) {
                $line .= $this->cell($colors[$y][$x]);
            }
            $lines[] = $line.'|';
        }
        $lines[] = '+'.str_repeat('-', $state->width * 2).'+';

        return $lines;
    }

    /**
     * @return string[]
     */
    private function renderSidePanel(GameViewState $state): array
    {
        return [
            "\033[1mScore\033[0m",
            (string) $state->score,
            '',
            "\033[1mStage\033[0m",
            (string) $state->stage,
            '',
            "\033[1mNext\033[0m",
            ...$this->renderBlockPreview($state->nextBlock),
        ];
    }

    /**
     * @return string[]
     */
    private function renderBlockPreview(?AbstractBlock $block): array
    {
        $grid = array_fill(0, 4, array_fill(0, 4, 'black'));

        if (null === $block) {
            return array_map(fn (array $row): string => implode('', array_map($this->cell(...), $row)), $grid);
        }

        $coordinates = $block->getVisibleCoordinates();
        $minX = min(array_column($coordinates, 'x'));
        $minY = min(array_column($coordinates, 'y'));

        foreach ($coordinates as $coordinate) {
            $x = $coordinate['x'] - $minX;
            $y = $coordinate['y'] - $minY;
            if ($x >= 0 && $x < 4 && $y >= 0 && $y < 4) {
                $grid[$y][$x] = $block->getColor();
            }
        }

        return array_map(fn (array $row): string => implode('', array_map($this->cell(...), $row)), $grid);
    }

    private function cell(string $color): string
    {
        $code = match ($color) {
            'red' => 41,
            'magenta' => 45,
            'yellow' => 43,
            'cyan' => 46,
            'blue' => 44,
            'green' => 42,
            default => 40,
        };

        return "\033[{$code}m  \033[0m";
    }
}
