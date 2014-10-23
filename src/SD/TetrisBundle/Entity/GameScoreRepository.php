<?php

namespace SD\TetrisBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * @author Scott Driscoll <scott.driscoll@opensoftdev.com>
 */
class GameScoreRepository extends EntityRepository
{
    /**
     * @param GameScore $entity
     */
    public function store(GameScore $entity)
    {
        $this->_em->persist($entity);
        $this->_em->flush();
    }
}
