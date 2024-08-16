<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findBestMatchingByName(string $name): ?Game
    {
        $qb = $this
            ->createQueryBuilder('g')
            ->addSelect('LEVENSHTEIN(:inputName, g.name) AS HIDDEN levenshtein_distance')
            ->where('SOUNDEX(g.name) = SOUNDEX(:inputName)')
            ->setParameter('inputName', $name)
            ->orderBy('levenshtein_distance', 'ASC')
            ->setMaxResults(1);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result ?? null;
    }
}
