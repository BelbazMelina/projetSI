<?php

namespace App\Repository;

use App\Entity\Molecule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Molecule>
 */
class MoleculeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Molecule::class);
    }
    public function findRandomMolecule(int $planteId): ?Molecule
    {
        $count = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.plante = :planteId')
            ->setParameter('planteId', $planteId)
            ->getQuery()
            ->getSingleScalarResult();

        if ($count == 0) {
            return null;
        }

        $randomOffset = rand(0, $count - 1);

        return $this->createQueryBuilder('m')
            ->where('m.plante = :planteId')
            ->setParameter('planteId', $planteId)
            ->setMaxResults(1)
            ->setFirstResult($randomOffset)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    public function findRandomMolecule(int $planteId): ?Molecule
//    {
//        return $this->createQueryBuilder('m')
//            ->where('m.plante = :planteId')
//            ->setParameter('planteId', $planteId)
//            ->orderBy('RANDOM()')
//            ->setMaxResults(1)
//            ->getQuery()
//            ->getOneOrNullResult();
//    }

    //    /**
    //     * @return Molecule[] Returns an array of Molecule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Molecule
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
