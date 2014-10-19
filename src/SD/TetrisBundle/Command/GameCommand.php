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
use SD\Game\Sockets\Udp2p;
use SD\TetrisBundle\Events;
use SD\TetrisBundle\Event\PeerLoseEvent;
use SD\ConsoleHelper\OutputHelper;

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

        $question = new ChoiceQuestion('Singleplayer or multiplayer?', array (1 => 'single (default)', 2 => 'multi'), 1);
        if ('multi' ===  $helper->ask($input, $output, $question)) {
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

        if ($this->userWin) {
            $output->writeln("\n\n<fg=green>*** You win!! ***\n\n</fg=green>");
        } else {
            $output->writeln("\n\n<fg=red>*** You lose. ***\n\n</fg=red>");
        }
    }

    /**
     * @DI\Observe(Events::MESSAGE_PEER_LOSE, priority = 0)
     *
     * @param PeerLoseEvent $event
     */
    public function peerLose(PeerLoseEvent $event)
    {
        $this->userWin = true;
    }
}
