<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SD\ConsoleHelper\OutputHelper;
use SD\Game\Engine as GameEngine;

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
        $outputHelper = new OutputHelper($output);
        $outputHelper->disableKeyboardOutput();
        $outputHelper->hideCursor();

        $this->getContainer()->get('game.game_board')->initialize($outputHelper);

        $this->getContainer()->get('game.engine')->run();
    }
}
