<?php

namespace App\Repository;

use App\Entity\GcuWeb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method GcuWeb|null find($id, $lockMode = null, $lockVersion = null)
 * @method GcuWeb|null findOneBy(array $criteria, array $orderBy = null)
 * @method GcuWeb[]    findAll()
 * @method GcuWeb[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GcuWebRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, GcuWeb::class);
    }

//    /**
//     * @return GcuWeb[] Returns an array of GcuWeb objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GcuWeb
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
