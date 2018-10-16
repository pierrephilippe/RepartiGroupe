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
	
	public function nombreParticipants($values, $limite)
	{
		// in your repository
		/*
		SELECT c.annee, c.champion
		FROM championnats c
		INNER JOIN
		    (
		        SELECT champion, COUNT(*) AS nbrChampionnats
		        FROM championnats
		        GROUP BY champion
		    ) a
		    ON a.champion = c.champion
		ORDER BY nbrChampionnats, champion, annee;
		*/


		return $this->createQueryBuilder('a')
		    ->andWhere('a.status = :val')
            ->setParameter('val', $values['status'])
            ->andWhere('a.atelier = :val')
            ->setParameter('val', $values['atelier'])
		    //->innerjoin('a.atelier', 'b')
		    //->addselect('count(b) as compteur')
		    //->orderBy('b.compteur', 'DESC')
			->setMaxResults($limite)
            ->getQuery()
            ->getResult()
        ;
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
