<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Command;

use SD\Game\Sockets\Udp2p;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SD\ConsoleHelper\OutputHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('tetris:launch');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $name = null;

        $question = new ChoiceQuestion('Singleplayer or multiplayer?', array (1 => 'single (default)', 2 => 'multi'), 1);
        if ('multi' ===  $helper->ask($input, $output, $question)) {
            $question = new Question('Enter your name: ');
            do {
                $name = $helper->ask($input, $output, $question);
            } while (empty($name));

            $question = new Question('Enter IP address to connect to: ');
            $ipAddress = $helper->ask($input, $output, $question);

            /** @var Udp2p $udp */
            $udp = $this->getContainer()->get('game.udp2p');

            if (!$udp->establishCommunication($ipAddress, $port = 11009, $timeout = 60000, $name)) {
                $output->writeln("Could not connect to peer.");

                return;
            }
        }

        $outputHelper = new OutputHelper($output);
        $outputHelper->disableKeyboardOutput();
        $outputHelper->hideCursor();

        $this->getContainer()->get('game.game_board')->initialize($outputHelper, $name);

        $this->getContainer()->get('game.engine')->run();
    }
}
