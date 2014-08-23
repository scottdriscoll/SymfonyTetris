<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\TetrisBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SD\ConsoleHelper\OutputHelper;

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


        $output->writeln("<bg=yellow>    </bg=yellow>\n");
        $output->writeln("<bg=yellow> </bg=yellow>");
        $output->writeln("<bg=yellow> </bg=yellow>");
        $output->writeln("<bg=yellow> </bg=yellow>");
        $output->writeln("<bg=yellow> </bg=yellow>\n");

        $output->writeln("<bg=yellow>  </bg=yellow>");
        $output->writeln("<bg=yellow> </bg=yellow>");
        $output->writeln("<bg=yellow> </bg=yellow>");
        $output->writeln("<bg=yellow> </bg=yellow>\n");

        $output->writeln("<bg=yellow>  </bg=yellow>");
        $output->writeln(" <bg=yellow>  </bg=yellow>\n");
    }
}
