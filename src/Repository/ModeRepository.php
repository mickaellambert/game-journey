<?php

namespace App\Repository;

use App\Entity\Mode;
use App\Service\EntityFactoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Mode>
 */
class ModeRepository extends ServiceEntityRepository implements EntityFactoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Mode::class);
    }

    public function findOrCreate(string $name): Mode
    {
        $mode = $this->findOneBy(['name' => $name]);
        
        if (!$mode) {
            $mode = new Mode();
            $mode->setName($name);
            $this->getEntityManager()->persist($mode);
        }
        
        return $mode;
    }
}
