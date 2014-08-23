<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\OutputHelper;
use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class OBlock extends AbstractBlock
{
    /**
     * @var string
     */
    private $color = 'cyan';

    /**
     * @var array
     */
    private $shape = [
        '..',
        '..'
    ];

}