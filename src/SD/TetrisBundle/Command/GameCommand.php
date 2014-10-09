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
        $a = new \SD\TetrisBundle\Event\GameOverEvent();
        $b = clone $a;

        $hash1 = spl_object_hash($a);
        $hash2 = spl_object_hash($b);
        var_dump($hash1, $hash2);

        $s1 = serialize($a);
        var_dump($s1);
        $o1 = unserialize($s1);
        $h1 = spl_object_hash($o1);
        var_dump($h1);
        exit;

        $outputHelper = new OutputHelper($output);
        $outputHelper->disableKeyboardOutput();
        $outputHelper->hideCursor();

        $this->getContainer()->get('game.game_board')->initialize($outputHelper);

        $this->getContainer()->get('game.engine')->run();
    }
}
