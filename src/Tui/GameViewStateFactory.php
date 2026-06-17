<?php

namespace App\Tui;

use App\Game\ActiveBlockManager;
use App\Game\Block\AbstractBlock;
use App\Game\GameBoard;
use App\Game\NextBlockManager;
use App\Game\ScoreManager;

final readonly class GameViewStateFactory
{
    public function __construct(
        private GameBoard $gameBoard,
        private ActiveBlockManager $activeBlockManager,
        private NextBlockManager $nextBlockManager,
        private ScoreManager $scoreManager,
        private int $boardWidth,
        private int $boardHeight,
    ) {
    }

    public function create(bool $gameOver = false): GameViewState
    {
        $board = $this->gameBoard->getBoard();
        $activeBlock = $this->activeBlockManager->getActiveBlock();
        $landingPreviewEnabled = $this->gameBoard->isLandingPreviewEnabled();
        $landingPreviewBlock = null === $activeBlock || !$landingPreviewEnabled ? null : $this->gameBoard->getLandingPreviewBlock($activeBlock);
        $nextBlock = $this->nextBlockManager->peekNextBlock();
        $score = $this->scoreManager->getPlayerScore();
        $stage = $this->scoreManager->getPlayerStage();

        return new GameViewState(
            $board,
            $activeBlock,
            $landingPreviewBlock,
            $nextBlock,
            $score,
            $stage,
            $this->boardWidth,
            $this->boardHeight,
            $this->buildSignature($board, $activeBlock, $nextBlock, $score, $stage, $landingPreviewEnabled, $gameOver),
            $landingPreviewEnabled,
            $gameOver,
        );
    }

    private function buildSignature(array $board, ?AbstractBlock $activeBlock, ?AbstractBlock $nextBlock, int $score, int $stage, bool $landingPreviewEnabled, bool $gameOver): string
    {
        $parts = [$score, $stage, $landingPreviewEnabled ? '1' : '0', $gameOver ? '1' : '0'];

        foreach ($board as $row) {
            foreach ($row as $unit) {
                $parts[] = $unit->getColor();
            }
        }

        foreach ([$activeBlock, $nextBlock] as $block) {
            if (null === $block) {
                $parts[] = 'none';
                continue;
            }

            $parts[] = $block->getColor();
            foreach ($block->getVisibleCoordinates() as $coordinate) {
                $parts[] = $coordinate['x'].':'.$coordinate['y'];
            }
        }

        return hash('xxh3', implode('|', $parts));
    }
}
