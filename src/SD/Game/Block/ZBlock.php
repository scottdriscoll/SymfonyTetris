<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class ZBlock extends AbstractBlock
{
    public function __construct()
    {
        $this->color = 'white';

        $this->block = [
            [
                'length' => 3,
                'height' => 2,
                'shapes' => [
                    '..',
                    ' ..'
                ]
            ],
            [
                'length' => 2,
                'height' => 3,
                'shapes' => [
                    ' .',
                    '..',
                    '.'
                ]
            ]
        ];
    }
}
