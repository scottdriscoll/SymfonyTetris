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
     * @var array
     */
    private $shapes = [
        [
            'O',
            'O',
            'O',
            'O'
        ],
        [
            'OOOO'
        ]
    ];

}
