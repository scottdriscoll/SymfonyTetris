<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use SD\ConsoleHelper\ScreenBuffer;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class SBlock extends AbstractBlock
{
    /**
     * @var string
     */
    private $color = 'blue';

    public function __construct()
    {
        $this->block = [
            [
                'offset' => -1,
                'shapes' => [
                    ' ..',
                    '..'
                ]
            ],
            [
                'offset' => 0,
                'shapes' => [
                    '.',
                    '..',
                    ' .'
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
