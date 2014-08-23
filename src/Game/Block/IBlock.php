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
class IBlock extends AbstractBlock
{
    /**
     * @var string
     */
    private $color = 'red';

    /**
     * @var int
     */
    private $currentIndex = 0;

    /**
     * @var array
     */
    private $shapes = [
        [
            '.',
            '.',
            '.',
            '.'
        ],
        [
            '....'
        ]
    ];

}
