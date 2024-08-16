<?php

namespace App\Repository;

use App\Entity\Platform;
use App\Service\EntityFactoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Platform>
 */
class PlatformRepository extends ServiceEntityRepository implements EntityFactoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Platform::class);
    }
    
    public function findOrCreate(array $data): Platform
    {
        $platform = $this->findOneBy($data);
        
        if (!$platform) {
            $platform = new Platform();
            $platform->setIgdbId($data['igdbId']);
            $platform->setName($data['name']);

            $this->getEntityManager()->persist($platform);
            $this->getEntityManager()->flush();
        }
        
        return $platform;
    }
}
