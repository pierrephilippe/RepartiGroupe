<?php

namespace App\Calculateur;

use Doctrine\ORM\EntityManager;

use App\Entity\EleveAtelier;

class Groupe
{
	private $em;

  	public function __construct(EntityManager $em) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
  	}

	public function calcul()
	{
		/*
		 * Etape 1 : on récupère toutes les demandes "ELEVE - ATELIER"
		 *			 on vérifie que chque élève a bien 3 ateliers
		 *			 on compte les récurence de chaque atelier et on trie du + grand au - grand nbre
		 */
		 $eleveateliers = $this->em->getRepository(EleveAtelier::class)->findAll();
		 return $eleveateliers;
	}
}