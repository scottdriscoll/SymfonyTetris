<?php

namespace App\Repository;

use App\Entity\GameScore;
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
        $entityManager = $this->getEntityManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }
}
