<?php

namespace App\Repository;

use App\Entity\Genre;
use App\Service\EntityFactoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Genre>
 */
class GenreRepository extends ServiceEntityRepository implements EntityFactoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Genre::class);
    }

    public function findOrCreate(string $name): Genre
    {
        $genre = $this->findOneBy(['name' => $name]);
        
        if (!$genre) {
            $genre = new Genre();
            $genre->setName($name);
            $this->getEntityManager()->persist($genre);
        }
        
        return $genre;
    }
}
