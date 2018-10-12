<?php

namespace App\Repository;

use App\Entity\EleveAtelier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EleveAtelier|null find($id, $lockMode = null, $lockVersion = null)
 * @method EleveAtelier|null findOneBy(array $criteria, array $orderBy = null)
 * @method EleveAtelier[]    findAll()
 * @method EleveAtelier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EleveAtelierRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EleveAtelier::class);
    }

//    /**
//     * @return EleveAtelier[] Returns an array of EleveAtelier objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EleveAtelier
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
