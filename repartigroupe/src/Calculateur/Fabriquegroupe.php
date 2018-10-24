<?php

namespace App\Calculateur;

use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\Common\Collections\ArrayCollection;
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
		set_time_limit(600);
		//initialisation barre de progression
		$compteur = 0;
		$percent = 0;
		$total = count($this->em->getRepository(EleveAtelier::class)->findAll());
		

		/*
		 * Etape 1 : Trie des ateliers par demandes
		 */
		 
		 //on vérifie au préalable que le traitement n'a pas déjà été fait :
		 $groupes = $this->em->getRepository(Groupe::class)->findAll();
		 if(count($groupes) == 0){
			 $ateliers = $this->em->getRepository(Atelier::class)->findAll();

			 //On trie les ateliers du plus demandé au moins demandé
			 foreach($ateliers as $key=>$atelier){
				$nombre[$key] = $atelier->getEleveAteliers()->count();
				$nbr_participants[$key] = floor($nombre[$key]/3)+1;	

				//on en profite pour créer les groupes
				$groupe1 = new Groupe;
				$groupe1->setAtelier($ateliers[$key]);
				$groupe1->setNbparticipant($nbr_participants[$key]);
				$groupe1->setNom("GROUPE 1 ".$ateliers[$key]->getNom());

				//on en profite pour créer les groupes
				$groupe2 = new Groupe;
				$groupe2->setAtelier($ateliers[$key]);
				$groupe2->setNbparticipant($nbr_participants[$key]);
				$groupe2->setNom("GROUPE 2 ".$ateliers[$key]->getNom());

				//on en profite pour créer les groupes
				$groupe3 = new Groupe;
				$groupe3->setAtelier($ateliers[$key]);
				$groupe3->setNbparticipant($nbr_participants[$key]);
				$groupe3->setNom("GROUPE 3 ".$ateliers[$key]->getNom());

				$atelier->setNbparticipant($nbr_participants[$key]);
				$this->em->persist($atelier);
				$this->em->persist($groupe1);
				$this->em->persist($groupe2);
				$this->em->persist($groupe3);
				$this->em->flush();
			 }
			 array_multisort($nbr_participants, SORT_ASC, $ateliers);
		 }
		  
			
		 //A ce stade, tous les 1ers groupes sont créés, les élèves ne sont pas affectés
		 //On fait 3 boucles pour affecter les élèves dans les 3 groupes
		 for($tour = 1; $tour <=3; $tour++){
			 
			 //dump($tour);

			 //TOUS LES ELEVES
			 $nombre_eleves = count($this->em->getRepository(Eleve::class)->findAll()) * $tour;
			 //dump($nombre_eleves);

			 //Tant que tous les élèves ne sont pas affectés au groupe
			 while($nombre_eleves > count($this->em->getRepository(EleveGroupe::class)->findAll()))
			 {
				
				//on parcourt les groupes
				//on les tries par place restante
				$groupes = $this->em->getRepository(Groupe::class)->findByNomBegin('GROUPE '.$tour);

				 //On trie les ateliers du plus demandé au moins demandé
				 foreach($groupes as $key=>$groupe){
					$nombre[$key] = $groupe->getNbparticipant();
					$nbr_participants[$key] = floor($nombre[$key]/3)+1;	
				 }
				 
				 if($tour == 1){
				 	array_multisort($nbr_participants, SORT_ASC, $groupes);
				 }
				 if($tour == 2){
					array_multisort($nbr_participants, SORT_DESC, $groupes);
				 }




				
				//on parcourt les ateliers par ordres de demandes
			 	foreach($groupes as $num_groupe=>$groupe)
			 	{
				 	$status = $tour-1;
					$status.= "passage";
					//on cherche les ateliers correspondant 
				 	$atelier = $this->em->getRepository(Atelier::class)->findOneByNom(substr($groupe->getNom(),9));
					
					
					if($groupe->getNbparticipant() > 0){
						$selection = round($groupe->getNbparticipant()*0.8);
					} else {
						$selection = 1;
					}
				 	//on extrait 10% des demandes non traites pour l'atelier en cours
				 	$eleveatelier_restant = $this->em->getRepository(EleveAtelier::class)
				 								  	 ->findBy(array(
				 								  	 	'atelier'=>$atelier->getId(),
				 								  	 	'status'=> $status),
				 								  	 	null,
				 								  	 	$selection
				 								  	 	//round($nbr_participants[$num_atelier]*0.5) //limit +10%
				 	);

				 	
				 	
				 	//on ajoute les élèves trouvés ci-dessus dans le groupe
					foreach($eleveatelier_restant as $un_participant){
						$une_participation = new EleveGroupe;
						$une_participation->setGroupe($groupe);								//affecte groupe
						$une_participation->setEleve($un_participant->getEleve());			//affecte eleve
						$une_participation->setQuestion($un_participant->getQuestion());	//ajoute question
						$this->em->persist($une_participation);								
						
						//on change le status des souhaits de l'élèves pour dire que le 1er choix est fait
						$tous_choix_de_l_eleve = $this->em->getRepository(EleveAtelier::class)
														  ->findByEleve($un_participant->getEleve());
						foreach($tous_choix_de_l_eleve as $un_choix_de_l_eleve){
							$un_choix_de_l_eleve->setStatus($tour."passage");
							$this->em->persist($un_choix_de_l_eleve);
						}
						//on supprime le choix qui est maintenant pris en compte
						$this->em->remove($un_participant);
						
						//on met à jour le nombre de place dans le groupe
						$groupe->setNbparticipant($groupe->getNbparticipant()-1);
						$this->em->persist($groupe);

						//Enregistrement en BDD
			    		$this->em->flush();
			    		
			    		//POUR LA BARRE DE PROGRESSION
						$compteur ++;
						$session = new Session();
						$pourcent = round($compteur*100/$total);
						$session->set('progress',$pourcent);
						$session->set('compteur',$compteur);
						$session->save();

					 }
				  }
			 }
		}

		return true;
	}

	public function calcul_ok()
	{
		//initialisation barre de progression
		$compteur = 0;
		$percent = 0;
		$total = count($this->em->getRepository(EleveAtelier::class)->findAll());
		

		/*
		 * Etape 1 : Trie des ateliers par demandes
		 */
		 
		 //on vérifie au préalable que le traitement n'a pas déjà été fait :
		 $groupes = $this->em->getRepository(Groupe::class)->findAll();
		 if(count($groupes) == 0){
			 $ateliers = $this->em->getRepository(Atelier::class)->findAll();

			 //On trie les ateliers du plus demandé au moins demandé
			 foreach($ateliers as $key=>$atelier){
				$nombre[$key] = $atelier->getEleveAteliers()->count();
				$nbr_participants[$key] = floor($nombre[$key]/3)+1;	

				//on en profite pour créer les groupes
				$groupe1 = new Groupe;
				$groupe1->setAtelier($ateliers[$key]);
				$groupe1->setNbparticipant($nbr_participants[$key]);
				$groupe1->setNom("GROUPE 1 ".$ateliers[$key]->getNom());

				//on en profite pour créer les groupes
				$groupe2 = new Groupe;
				$groupe2->setAtelier($ateliers[$key]);
				$groupe2->setNbparticipant($nbr_participants[$key]);
				$groupe2->setNom("GROUPE 2 ".$ateliers[$key]->getNom());

				//on en profite pour créer les groupes
				$groupe3 = new Groupe;
				$groupe3->setAtelier($ateliers[$key]);
				$groupe3->setNbparticipant($nbr_participants[$key]);
				$groupe3->setNom("GROUPE 3 ".$ateliers[$key]->getNom());

				$atelier->setNbparticipant($nbr_participants[$key]);
				$this->em->persist($atelier);
				$this->em->persist($groupe1);
				$this->em->persist($groupe2);
				$this->em->persist($groupe3);
				$this->em->flush();
			 }
			 array_multisort($nbr_participants, SORT_DESC, $ateliers);
		 }
		  
			
		 //A ce stade, tous les 1ers groupes sont créés, les élèves ne sont pas affectés
		 //On fait 3 boucles pour affecter les élèves dans les 3 groupes
		 for($tour = 1; $tour <=3; $tour++){
			 
			 //dump($tour);

			 //TOUS LES ELEVES
			 $nombre_eleves = count($this->em->getRepository(Eleve::class)->findAll()) * $tour;
			 //dump($nombre_eleves);

			 //Tant que tous les élèves ne sont pas affectés au groupe
			 while($nombre_eleves > count($this->em->getRepository(EleveGroupe::class)->findAll()))
			 {
				
				
				//on parcourt les ateliers par ordres de demandes
			 	foreach($ateliers as $num_atelier=>$atelier)
			 	{
				 	$status = $tour-1;
					$status.= "passage";
					//on cherche le groupe correspondant 
				 	$groupe = $this->em->getRepository(Groupe::class)->findOneByNom("GROUPE ".$tour." ".$ateliers[$num_atelier]->getNom());

					
					if($groupe->getNbparticipant() > 0){
						$selection = round($groupe->getNbparticipant()*0.8);
					} else {
						$selection = 1;
					}
				 	//on extrait 10% des demandes non traites pour l'atelier en cours
				 	$eleveatelier_restant = $this->em->getRepository(EleveAtelier::class)
				 								  	 ->findBy(array(
				 								  	 	'atelier'=>$ateliers[$num_atelier]->getId(),
				 								  	 	'status'=> $status),
				 								  	 	null,
				 								  	 	$selection
				 								  	 	//round($nbr_participants[$num_atelier]*0.5) //limit +10%
				 	);

				 	
				 	
				 	//on ajoute les élèves trouvés ci-dessus dans le groupe
					foreach($eleveatelier_restant as $un_participant){
						$une_participation = new EleveGroupe;
						$une_participation->setGroupe($groupe);								//affecte groupe
						$une_participation->setEleve($un_participant->getEleve());			//affecte eleve
						$une_participation->setQuestion($un_participant->getQuestion());	//ajoute question
						$this->em->persist($une_participation);								
						
						//on change le status des souhaits de l'élèves pour dire que le 1er choix est fait
						$tous_choix_de_l_eleve = $this->em->getRepository(EleveAtelier::class)
														  ->findByEleve($un_participant->getEleve());
						foreach($tous_choix_de_l_eleve as $un_choix_de_l_eleve){
							$un_choix_de_l_eleve->setStatus($tour."passage");
							$this->em->persist($un_choix_de_l_eleve);
						}
						//on supprime le choix qui est maintenant pris en compte
						$this->em->remove($un_participant);
						
						//on met à jour le nombre de place dans le groupe
						$groupe->setNbparticipant($groupe->getNbparticipant()-1);
						$this->em->persist($groupe);

						//Enregistrement en BDD
			    		$this->em->flush();
			    		
			    		//POUR LA BARRE DE PROGRESSION
						$compteur ++;
						$session = new Session();
						$pourcent = round($compteur*100/$total);
						$session->set('progress',$pourcent);
						$session->set('compteur',$compteur);
						$session->save();

					 }
				  }
			 }
		}

		return true;
	}
}