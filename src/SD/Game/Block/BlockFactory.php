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
     * @param int $index
     * @return AbstractBlock
     * @throws \InvalidArgumentException
     */
    public function getBlock($index)
    {
        if ($index >= count($this->blockTypes)) {
            throw new \InvalidArgumentException('bad index');
        }

        $class = '\\SD\\Game\\Block\\' . $this->blockTypes[$index] . 'Block';

        return new $class();
    }

    /**
     * @return AbstractBlock
     */
    public function getRandomBlock()
    {
        return $this->getBlock(rand(0, (count($this->blockTypes) - 1)));
    }
}
