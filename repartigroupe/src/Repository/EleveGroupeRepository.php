<?php

namespace App\Repository;

use App\Entity\EleveGroupe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method EleveGroupe|null find($id, $lockMode = null, $lockVersion = null)
 * @method EleveGroupe|null findOneBy(array $criteria, array $orderBy = null)
 * @method EleveGroupe[]    findAll()
 * @method EleveGroupe[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EleveGroupeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, EleveGroupe::class);
    }

//    /**
//     * @return EleveGroupe[] Returns an array of EleveGroupe objects
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
    public function findOneBySomeField($value): ?EleveGroupe
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
