<?php

namespace App\Repository;

use App\Entity\Theme;
use App\Service\EntityFactoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Theme>
 */
class ThemeRepository extends ServiceEntityRepository implements EntityFactoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Theme::class);
    }

    public function findOrCreate(array $data): Theme
    {
        $theme = $this->findOneBy($data);
        
        if (!$theme) {
            $theme = new Theme();
            $theme->setIgdbId($data['igdbId']);
            $theme->setName($data['name']);

            $this->getEntityManager()->persist($theme);
            $this->getEntityManager()->flush();
        }
        
        return $theme;
    }
}
