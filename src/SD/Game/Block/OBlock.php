<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class OBlock extends AbstractBlock
{
    public function __construct()
    {
        $this->color = 'cyan';

        $this->block = [
            [
                'length' => 2,
                'height' => 2,
                'shapes' => [
                    '..',
                    '..'
                ]
            ]
        ];
    }
}
