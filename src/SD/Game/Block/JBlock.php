<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class JBlock extends AbstractBlock
{
    public function __construct()
    {
        $this->color = 'magenta';

        $this->block = [
            [
                'length' => 2,
                'height' => 3,
                'shapes' => [
                    ' .',
                    ' .',
                    '..'
                ]
            ],
            [
                'length' => 3,
                'height' => 2,
                'shapes' => [
                    '.',
                    '...'
                ]
            ],
            [
                'length' => 2,
                'height' => 3,
                'shapes' => [
                    '..',
                    '.',
                    '.'
                ]
            ],
            [
                'length' => 3,
                'height' => 2,
                'shapes' => [
                    '...',
                    '  .'
                ]
            ]
        ];
    }
}
