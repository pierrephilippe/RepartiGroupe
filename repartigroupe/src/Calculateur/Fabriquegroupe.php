<?php

namespace App\Calculateur;

use Doctrine\ORM\EntityManager;

use App\Entity\Eleve;
use App\Entity\Atelier;
use App\Entity\EleveAtelier;

use App\Entity\Groupe;
use App\Entity\EleveGroupe;

class Fabriquegroupe
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
		 $ateliers = $this->em->getRepository(Atelier::class)->findAll();
		 

		 //On trie les ateliers du plus demandé au moins demandé
		 foreach($ateliers as $key=>$atelier){
			$nombre[$key] = $atelier->getEleveAteliers()->count();
			$nbr_participants[$key] = round($nombre[$key]/3)+1;	
		 }
		 array_multisort($nombre, SORT_DESC, $ateliers);
		 array_multisort($nbr_participants, SORT_DESC, $ateliers);
		
		 //$ateliers triés
		 //dump($ateliers);
		 //dump($nbr_participants);
		  
		 //CALCUL DES GROUPE 1
		 //Selection des "n=nbr_participants" premiers participants à l'atelier 

		 $listes_groupe = $this->em->getRepository(EleveAtelier::class)->findAllOrdered();

		 foreach($ateliers as $key2=>$atelier){
			 
			 $listes_groupe = $this->em->getRepository(EleveAtelier::class)
			 					   ->findByAtelier($ateliers[$key]->getId(),  //id_atelier
			 										null,						//orderby
			 										$nbr_participants[$key]		//limit
			 										);
			 
			 
			 
			 dump($ateliers[$key2]->getId());
			 dump($nbr_participants[$key2]);
			
			 
			 dump($listes_groupe);
			
			 //On crée un nouveau Groupe
			 $participation = new Groupe;
			 $participation->setAtelier($ateliers[$key2]);
			 $participation->setNom("GROUPE 1 ".$ateliers[$key]->getNom());
			 $this->em->persist($participation);

			 foreach($listes_groupe as $un_participant){
				//dump($un_participant);
				$une_participation = new EleveGroupe;
				$une_participation->setGroupe($participation);
				$une_participation->setEleve($un_participant->getEleve());
				$une_participation->setQuestion($un_participant->getQuestion());
				$this->em->persist($une_participation);
				$this->em->remove($un_participant);
				//Enregistrement en BDD
	    		//$this->em->flush();
			 }
		 }
		
			
		 

		 return $ateliers;
	}
}