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
    /**
     * @var string
     */
    private $color = 'cyan';

    public function __construct()
    {
        $this->block = [
            [
                'offset' => 0,
                'shapes' => [
                    '..',
                    '..'
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
