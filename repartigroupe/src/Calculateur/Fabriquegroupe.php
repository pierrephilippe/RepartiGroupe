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
	private $compteur;
	private $percent;
	private $total;
	private $session;

  	public function __construct(EntityManager $em) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
  	}
	
	public function calcul()
	{	
		ini_set('max_execution_time', 600); //10 minutes de calcul
		ini_set('memory_limit', '-1');
		//initialisation barre de progression
		$this->compteur = 0;
		$this->percent = 0;
		$this->total = count($this->em->getRepository(EleveAtelier::class)->findAll());
		
		//on parcours 3 fois la liste de souhaits.
		// à chaque tour, on choisi le choix le plus demandé.
		for($tour = 1; $tour <=3; $tour++){
			//on trie les élèves par demandeurs les plus nombreaux tout atelier confondu.
			$eleves = $this->em->getRepository(Eleve::class)->findAll();
			foreach($eleves as $key=>$eleve)
			{
				$souhaits = $eleve->getEleveAteliers();
				$poids[$key]=0;
				foreach($souhaits as $souhait)
				{
					$atelier = $souhait->getAtelier()->getPoids();
					$poids[$key] += $atelier;
				}
			}
			array_multisort($poids, SORT_DESC, $eleves);
			//ici on a un tri des élèves par ordre d'atelier les plus demandés.


			//et maintenant, on parcours les élèves
			foreach($eleves as $key=>$eleve)
			{
				//maintenant pour chaque élève on cherche l'atelier le plus demandé qui n'est pas plein et on rempli.
				$souhaits = $eleve->getEleveAteliers();
		
				$index_souhaits= array();
				$places_restantes = array();
				foreach($souhaits as $key=>$souhait){
					$groupe = $this->em->getRepository(Groupe::class)->findOneByNom('GROUPE '.$tour.' '.$souhait->getAtelier()->getNom());
					$places_restantes[$key] = $groupe->getNbparticipant();
					$index_souhaits[$key] = $key;
				}
				array_multisort($places_restantes, SORT_DESC,
								$index_souhaits);
				//le meilleur choix se trouve maintenant à l'index 0

				//dump("on inscrit élève ".$eleve->getNom()." dans atelier ".$souhaits[$index_souhaits[0]]->getAtelier()->getNom()." pour le tour ".$tour);

				$this->affecte($souhaits[$index_souhaits[0]], $tour);
				//POUR LA BARRE DE PROGRESSION
				$this->compteur ++;
				$this->session = new Session();
				$this->pourcent = round($this->compteur*100/$this->total);
				$this->session->set('progress',$this->pourcent);
				$this->session->set('compteur',$this->compteur);
				$this->session->save();
			}
		}
		return true;
	}
	
	/* $delta représente le nombre d'élève manquant ou en trop dans un groupe pour que ce soit acceptable 
     * 
	 */
	public function ajuste()
	{
		ini_set('max_execution_time', 600); //10 minutes de calcul
		ini_set('memory_limit', '-1');
		
		$continue=true;
		$action = false;
		$ecart = 5; //Plus grand que 0 !!
		//$nbr_passage = 3;

		$eleves = $this->em->getRepository(Eleve::class)->findAll();


		//initialisation barre de progression
		$this->compteur = 0;
		$this->percent = 0;
		$this->total = $ecart*3*count($eleves);
		
		while($continue == true)
		{
			for($i=0;$i<3;$i++){
				//pour chaque élève on regarde si un meilleur choix est possible
				
				foreach($eleves as $eleve)
				{
					$inscriptions = $eleve->getEleveGroupes();	
					if(count($inscriptions) == 3)
					{
						/*foreach ($inscriptions as $key => $inscription) {
							
							//if(($inscription->getGroupe()->getNbparticipant()-$ecart) > 0){
							//	dump("pas assez d'élèves");
							//	dump("count1 : ".($inscription->getGroupe()->getNbparticipant()-$ecart));
							//	dump($eleve);
							//	dump($inscription);
							//	$this->permute($inscription, "ajoute");
							//}
							
							if (($inscription->getGroupe()->getNbparticipant()+$ecart) < 0) {
								$retour = $this->permute($inscription, $ecart);
								break;
							}
						}
						*/
						if (($inscriptions[$i]->getGroupe()->getNbparticipant()+$ecart) < 0) {
								$retour = $this->permute($inscriptions[$i], $ecart);
						}
					} else {
						//erreur de cohérence
						return false;
					}
					//POUR LA BARRE DE PROGRESSION
					$this->compteur ++;
					$this->session = new Session();
					$this->pourcent = round($this->compteur*100/$this->total);
					$this->session->set('progress',$this->pourcent);
					$this->session->set('compteur',$this->compteur);
					$this->session->save();
				}

			}
			if($ecart > 0){
				$ecart --;
			} else {
				$continue = false;
			}
		}
		return true;
	}


	private function permute(EleveGroupe $elevegroupe, $ecart)
	{
			//ex Abou-Haidar
			//GROUPE 3 Thème11: Stress au collège
			//nbparticipant: -16

			//Cherchons le plus grand écart positif dans les groupes restants :
			//GROUPE 1 Thème 11 ?
			//GROUPE 2 Thème 11 ?
			$inscriptions = $this->em->getRepository(EleveGroupe::class)->findByEleve($elevegroupe->getEleve());
			foreach ($inscriptions as $key => $inscription) {
				if($inscription->getId() != $elevegroupe->getId())
				{
					//GROUPE 1 Thème 1: Addiction aux stupéfiants
					$groupe = $this->em->getRepository(Groupe::class)->findOneByNom("GROUPE ".$inscription->getGroupe()->getNum().
																			 " ".$inscription->getGroupe()->getAtelier()->getNom());

					if($groupe->getNbparticipant() > 0)
					{
						//GROUPE 1 Thème 1: Addiction aux stupéfiants
						//nbparticipant: 1
						

						//je cherche GROUPE 3 Thème 1: Addiction aux stupéfiants
						$echangegroupe1 = $this->em->getRepository(Groupe::class)->findOneByNom("GROUPE ".$elevegroupe->getGroupe()->getNum().
																						  " ".$groupe->getAtelier()->getNom());
						//nbparticipant: 0


						//je cherche GROUPE 1 Thème11: Stress au collège
						$echangegroupe2 = $this->em->getRepository(Groupe::class)->findOneByNom("GROUPE ".$inscription->getGroupe()->getNum().
																						  " ".$elevegroupe->getGroupe()->getAtelier()->getNom());
						//nbparticipant: 11
						

						if(($echangegroupe1->getNbparticipant() >= -$ecart) && ($echangegroupe2->getNbparticipant() >= -$ecart))
						{
							//on valide l'échange
							
							//GROUPE 3 Thème11: Stress au collège
							//nbparticipant: -16 -> -15
							$elevegroupe->getGroupe()->setNbparticipant($elevegroupe->getGroupe()->getNbparticipant() + 1);
							
							//GROUPE 1 Thème 1: Addiction aux stupéfiants
							//nbparticipant: 1   -> 2
							$groupe->setNbparticipant($groupe->getNbparticipant()+1);
							
							//GROUPE 3 Thème 1: Addiction aux stupéfiants
							//nbparticipant: 0   -> -1
							$echangegroupe1->setNbparticipant($echangegroupe1->getNbparticipant()-1);

							//GROUPE 1 Thème11: Stress au collège
							//nbparticipant: 11  -> 10
							$echangegroupe2->setNbparticipant($echangegroupe2->getNbparticipant()-1);

							//inversion des groupes
							$elevegroupe->setGroupe($echangegroupe2);
							$inscription->setGroupe($echangegroupe1);

							$this->em->persist($elevegroupe);
							$this->em->persist($groupe);
							$this->em->persist($echangegroupe1);
							$this->em->persist($echangegroupe2);
							$this->em->persist($inscription);

							$this->em->flush();
							return true;
						}

					}
				}
			}
			return true;
	}

	public function ajuste_marchepas($ecart)
	{
		
		$continue = true;
		$cpt = 0;
		while($continue)
		{

			$groupes = $this->em->getRepository(Groupe::class)->findBy(
																	array(),
																	array('nbparticipant' => 'ASC')
			);


			$trop = array();
			$pasassez = array();
			foreach ($groupes as $key => $groupe) {
				
				if($groupe->getNbparticipant()+$ecart < 0)
				{
					//trop d'élèves !
					//$trop[] = $groupe->getId();
					$trop[] = $key;

				}
				if($groupe->getNbparticipant()-$ecart > 0)
				{
					// pas assez d'élèves !
					//$pasassez[] = $groupe->getId();
					$pasassez[] = $key;
				}
			}
			
			$actif = false;
			foreach($trop as $id_groupe_trop){
				//peut on inverser avec un membre de pas assez ?
				//GROUPE : $groupes[$id_groupe_trop];
				//foreach($pasassez as $id_groupe_pasassez){
					dump("y a t'il un élève qui a ces 2 ?");
					dump($groupes[$id_groupe_trop]->getNom());
					dump($groupes[$pasassez[0]]->getNom());
					dump("???????????????????????????????");

					$elevesgroupe = $this->em->getRepository(EleveGroupe::class)->findByGroupe($groupes[$id_groupe_trop]);
					foreach ($elevesgroupe as $key => $elevegroupe) {
						$elevegroupe2 = $this->em->getRepository(EleveGroupe::class)->findOneBy(array('groupe' => $groupes[$pasassez[0]],
																									'eleve' => $elevegroupe->getEleve()));
						if($elevegroupe2){
							$groupe1 = $this->em->getRepository(Groupe::class)->findOneByNom("GROUPE ".$elevegroupe2->getGroupe()->getNum().
																						  " ".$elevegroupe->getGroupe()->getAtelier()->getNom());
							$groupe2 = $this->em->getRepository(Groupe::class)->findOneByNom("GROUPE ".$elevegroupe->getGroupe()->getNum().
																						  " ".$elevegroupe2->getGroupe()->getAtelier()->getNom());

							$elevegroupe->getGroupe()->setNbparticipant($elevegroupe->getGroupe()->getNbparticipant()-1);
							$elevegroupe2->getGroupe()->setNbparticipant($elevegroupe2->getGroupe()->getNbparticipant()-1);

							$groupe1->setNbparticipant($groupe1->getNbparticipant()+1);
							$groupe2->setNbparticipant($groupe2->getNbparticipant()+1);

							$elevegroupe->setGroupe($groupe1);
							$elevegroupe2->setGroupe($groupe2);

							$this->em->persist($elevegroupe);
							$this->em->persist($elevegroupe2);

							$this->em->flush();
							dump("on a inversé");

							dump($elevegroupe);
							dump($elevegroupe->getEleve()->getNom());
							dump($elevegroupe2);
							dump($elevegroupe2->getEleve()->getNom());
							$actif = true;
							break;
						} 
					}
				//}
			}

			if((count($trop) <= 0 && $ecart > 0) || $actif == false){
				$ecart--;

			}
			elseif (count($trop) <= 0 || $ecart <= 0) {
				$continue=false;
			}
			dump($actif);
			dump($trop);
			dump($pasassez);
			dump($ecart);
			
			$cpt++;
			if($cpt>10){
				$continue=false;
			}
		}
		return true;
	}

	private function affecte(EleveAtelier $eleveatelier, $tour){
		$groupe = $this->em->getRepository(Groupe::class)->findOneByNom('GROUPE '.$tour.' '.$eleveatelier->getAtelier()->getNom());
		$une_participation = new EleveGroupe;
		$une_participation->setGroupe($groupe);								//affecte groupe
		$une_participation->setEleve($eleveatelier->getEleve());			//affecte eleve
		$une_participation->setQuestion($eleveatelier->getQuestion());	//ajoute question
		$this->em->persist($une_participation);	
		$this->em->remove($eleveatelier);

		$groupe->setNbparticipant($groupe->getNbparticipant() - 1);
		$this->em->persist($groupe);
		
		$this->em->flush();
	}

	public function calcul_ok()
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

	public function calcul_ok_bof()
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
		 $ateliers = $this->em->getRepository(Atelier::class)->findAll();
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