<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace App\Command;

use App\Game\Engine;
use App\Game\GameBoard;
use App\Game\LeaderBoardManager;
use App\Game\MultiPlayerController;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use App\Game\Sockets\Udp2p;
use SD\ConsoleHelper\OutputHelper;
use App\Entity\GameScore;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
#[AsCommand(name: 'tetris:launch', description: 'Launch Symfony Tetris.')]
class GameCommand extends Command
{
    /**
     * @var bool
     */
    private $userWin = false;

    public function __construct(
        private readonly GameBoard $gameBoard,
        private readonly Engine $engine,
        private readonly MultiPlayerController $multiPlayerController,
        private readonly LeaderBoardManager $leaderBoardManager,
        private readonly Udp2p $udp2p,
        private readonly int $boardWidth,
        private readonly int $boardHeight,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $name = null;

        $question = new ChoiceQuestion('Select from the menu:', array(
            1 => 'Singleplayer (default)',
            2 => 'Multiplayer',
            3 => 'Show Leaderboard'
        ), 1);

        $option = $helper->ask($input, $output, $question);

        if ('Show Leaderboard' === $option) {
            $this->showLeaderboard($output);

            return Command::SUCCESS;
        } elseif ('Multiplayer' ===  $option) {
            if ($this->boardWidth != 10 || $this->boardHeight != 20) {
                $output->writeln('Board must be 10x20 to play multiplayer');

                return Command::FAILURE;
            }
            $question = new Question('Enter your name: ');
            do {
                $name = $helper->ask($input, $output, $question);
            } while (empty($name));

            $question = new Question('Enter IP address to connect to: ');
            $ipAddress = $helper->ask($input, $output, $question);

            /** @var Udp2p $udp */
            $udp = $this->udp2p;

            if (!$udp->establishCommunication($ipAddress, $timeout = 60000, $name)) {
                $output->writeln("Could not connect to peer.");

                return Command::FAILURE;
            }
        }

        $outputHelper = new OutputHelper($output);
        $outputHelper->disableKeyboardOutput();
        $outputHelper->hideCursor();

        $this->gameBoard->initialize($outputHelper, $name);

        $this->engine->run();

        if ($this->multiPlayerController->didPlayerWin()) {
            $output->writeln("\n\n<fg=green>*** You win!! ***\n\n</fg=green>");
        } else {
            $output->writeln("\n\n<fg=red>*** You lose. ***\n\n</fg=red>");
        }

        return Command::SUCCESS;
    }

    /**
     * @param OutputInterface $output
     */
    private function showLeaderboard(OutputInterface $output)
    {
        $scores = $this->leaderBoardManager->getLeaderBoard();

        if (empty($scores)) {
            $output->writeln('You either have not played any games, or the database has not been created.');

            return;
        }

        $table = new Table($output);
        $table->setHeaders(['Score', 'Opponent Name', 'Opponent Score', 'Date']);

        /** @var GameScore $score */
        foreach ($scores as $score) {
            $table->addRow([
                $score->getScore(),
                $score->getOpponentName(),
                $score->getOpponentScore(),
                $score->getTimePlayed()->format(DATE_RFC1036)
            ]);
        }

        $table->render($output);
    }
}
