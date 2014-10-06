<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class IBlock extends AbstractBlock
{
    public function __construct()
    {
        $this->color = 'red';

        $this->block = [
            [
                'length' => 1,
                'height' => 4,
                'shapes' => [
                    '.',
                    '.',
                    '.',
                    '.'
                ]
            ],
            [
                'length' => 4,
                'height' => 1,
                'shapes' => [
                    '....'
                ]
            ]
        ];
    }
}
