<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class TBlock extends AbstractBlock
{
    /**
     * @var string
     */
    private $color = 'green';

    public function __construct()
    {
        $this->block = [
            [
                'offset' => -1,
                'shapes' => [
                    '...',
                    ' .'
                ]
            ],
            [
                'offset' => -1,
                'shapes' => [
                    ' .',
                    '..',
                    ' .'
                ]
            ],
            [
                'offset' => -1,
                'shapes' => [
                    ' .',
                    '...'
                ]
            ],
            [
                'offset' => -1,
                'shapes' => [
                    '.',
                    '..',
                    '.'
                ]
            ]
        ];
    }

    /**
     * @param ScreenBuffer $buffer
     */
    public function draw(ScreenBuffer $buffer)
    {

    }
}
