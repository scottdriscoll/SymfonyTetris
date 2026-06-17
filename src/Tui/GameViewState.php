<?php

namespace App\Tui;

use App\Game\Block\AbstractBlock;

final readonly class GameViewState
{
    public function __construct(
        public array $board,
        public ?AbstractBlock $activeBlock,
        public ?AbstractBlock $landingPreviewBlock,
        public ?AbstractBlock $nextBlock,
        public int $score,
        public int $stage,
        public int $width,
        public int $height,
        public string $signature,
        public bool $landingPreviewEnabled = true,
        public bool $gameOver = false,
    ) {
    }
}
