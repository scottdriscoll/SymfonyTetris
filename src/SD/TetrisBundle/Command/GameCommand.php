<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Command;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\TableHelper;
use SD\Game\Sockets\Udp2p;
use SD\ConsoleHelper\OutputHelper;
use SD\TetrisBundle\Entity\GameScore;

/**
 * @DI\Service
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameCommand extends ContainerAwareCommand
{
    /**
     * @var bool
     */
    private $userWin = false;

    protected function configure()
    {
        $this->setName('tetris:launch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

            return;
        } elseif ('Multiplayer' ===  $option) {
            if ($this->getContainer()->getParameter('board_width') != 10 || $this->getContainer()->getParameter('board_height') != 20) {
                $output->writeln('Board must be 10x20 to play multiplayer');

                return;
            }
            $question = new Question('Enter your name: ');
            do {
                $name = $helper->ask($input, $output, $question);
            } while (empty($name));

            $question = new Question('Enter IP address to connect to: ');
            $ipAddress = $helper->ask($input, $output, $question);

            /** @var Udp2p $udp */
            $udp = $this->getContainer()->get('game.udp2p');

            if (!$udp->establishCommunication($ipAddress, $timeout = 60000, $name)) {
                $output->writeln("Could not connect to peer.");

                return;
            }
        }

        $outputHelper = new OutputHelper($output);
        $outputHelper->disableKeyboardOutput();
        $outputHelper->hideCursor();

        $this->getContainer()->get('game.game_board')->initialize($outputHelper, $name);

        $this->getContainer()->get('game.engine')->run();

        if ($this->getContainer()->get('game.multiplayer_controller')->didPlayerWin()) {
            $output->writeln("\n\n<fg=green>*** You win!! ***\n\n</fg=green>");
        } else {
            $output->writeln("\n\n<fg=red>*** You lose. ***\n\n</fg=red>");
        }
    }

    /**
     * @param OutputInterface $output
     */
    private function showLeaderboard(OutputInterface $output)
    {
        $scores = $this->getContainer()->get('game.leaderboard_manager')->getLeaderBoard();

        if (empty($scores)) {
            $output->writeln('You either have not played any games, or the database has not been created.');

            return;
        }

        /** @var TableHelper $table */
        $table = $this->getHelper('table');
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
