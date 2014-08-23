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
class ZBlock extends AbstractBlock
{
    /**
     * @var string
     */
    private $color = 'white';

    /**
     * @var int
     */
    private $currentIndex = 0;

    /**
     * @var array
     */
    private $shapes = [
        [
            '..',
            ' ..'
        ],
        [
            ' .',
            '..',
            '.'
        ]
    ];

}
