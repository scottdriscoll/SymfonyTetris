<?php
/**
 * Copyright (c) Scott Driscoll
 */

namespace SD\Game\Block;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("game.block_factory")
 *
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class BlockFactory
{
    /**
     * @var array
     */
    private $blockTypes = [
        'I',
        'J',
        'L',
        'O',
        'S',
        'T',
        'Z'
    ];

    /**
     * @return AbstractBlock
     */
    public function getRandomBlock()
    {
        $class = '\\SD\\Game\\Block\\' . $this->blockTypes[rand(0, 7)] . 'Block';

        return new $class();
    }
}
