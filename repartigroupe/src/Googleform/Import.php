<?php

namespace App\Googleform;

use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\ORM\EntityManager;
use League\Csv\Reader;
use League\Csv\Statement;

use App\Entity\Eleve;
use App\Entity\Classe;
use App\Entity\Atelier;
use App\Entity\EleveAtelier;
use App\Entity\Groupe;

class Import
{
	private $em;

  	public function __construct(EntityManager $em) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
  	}

	public function csv($fichier)
	{
			
		$reader = Reader::createFromPath($fichier, 'r');
		$reader->setHeaderOffset(0);
		
		//initialisation barre de progression
		$compteur = 0;
		$percent = 0;
		$total = count(file($fichier));

		/*
		 * Dans l'entete du fichier google form, les champs ne sont pas unique.
		 * ceci pour transfomer l'entete en 1-champ, 2-champ, etc.
		 */
		$nettoieheader = function($value, $cpt){
			return $cpt."-".$value;
		};
		$headers = array_map($nettoieheader, $reader->getHeader(), array_keys($reader->getHeader()));
			
		
		/*
		 * Description du tableau de retour
		 * on ne garde que les champs remplis et on vire l'horodatage
		 */
		$retour=array();
		$retour[0]['nom'] 		= "NOM";			// index 0
		$retour[0]['prenom'] 	= "PRENOM";			// index 1
		$retour[0]['classe'] 	= "CLASSE";			// index 2
		$retour[0]['atelier1'] 	= "ATELIER1";		// index 3
		$retour[0]['question1'] = "QUESTION1";		// index 4
		$retour[0]['atelier2'] 	= "ATELIER2";		// index 5
		$retour[0]['question2'] = "QUESTION2";		// index 6
		$retour[0]['atelier3'] 	= "ATELIER3";		// index 7
		$retour[0]['question3'] = "QUESTION3";		// index 8

		foreach ($reader->getRecords($headers) as $key=>$records) {
			//pour virer la colonne horodateur
			$choix = array_slice($records,1);

			//alimentons le tableau retour en parcourant les éléments
			foreach ($choix as $record){
				if(strcmp($record,"") != 0){
					$retour[$key][] = $record;
				}
			}
			
			//création des objets :
			$eleve = new Eleve;
			$eleve->setNom($retour[$key][0]);
			$eleve->setPrenom($retour[$key][1]);
			$this->em->persist($eleve);

			//On cherche si la classe existe déjà
      		$classe = $this->em->getRepository(Classe::class)->findOneByNom($retour[$key][2]);
      		if (null === $classe) {
      			$classe = new Classe;
      			$classe->setNom($retour[$key][2]);
			}
			
			$eleve->setClasse($classe);
			$this->em->persist($classe);

			//On cherche si l'atelier1 existe déjà
      		$atelier1 = $this->em->getRepository(Atelier::class)->findOneByNom($retour[$key][3]);
      		if (null === $atelier1) {
      			$atelier1 = new Atelier;
      			$atelier1->setNom($retour[$key][3]);
      			$atelier1->setNbparticipant(0);
      			$this->em->persist($atelier1);
			}

			//On cherche si l'atelier2 existe déjà
      		$atelier2 = $this->em->getRepository(Atelier::class)->findOneByNom($retour[$key][5]);
      		if (null === $atelier2) {
      			$atelier2 = new Atelier;
      			$atelier2->setNom($retour[$key][5]);
      			$atelier2->setNbparticipant(0);
				$this->em->persist($atelier2);
			}

			//On cherche si l'atelier3 existe déjà
      		$atelier3 = $this->em->getRepository(Atelier::class)->findOneByNom($retour[$key][7]);
      		if (null === $atelier3) {
      			$atelier3 = new Atelier;
      			$atelier3->setNom($retour[$key][7]);
      			$atelier3->setNbparticipant(0);
      			$this->em->persist($atelier3);
			}
			

			$eleveatelier1 = new EleveAtelier;
			$eleveatelier1->setEleve($eleve);
			$eleveatelier1->setAtelier($atelier1);
			$eleveatelier1->setQuestion($retour[$key][4]);
			$eleveatelier1->setStatus("0passage");
			$this->em->persist($eleveatelier1);
			$atelier1->setNbparticipant($atelier1->getNbparticipant()+1);
			$this->em->persist($atelier1);

			$eleveatelier2 = new EleveAtelier;
			$eleveatelier2->setEleve($eleve);
			$eleveatelier2->setAtelier($atelier2);
			$eleveatelier2->setQuestion($retour[$key][6]);
			$eleveatelier2->setStatus("0passage");
			$this->em->persist($eleveatelier2);
			$atelier2->setNbparticipant($atelier2->getNbparticipant()+1);
			$this->em->persist($atelier2);
			
			$eleveatelier3 = new EleveAtelier;
			$eleveatelier3->setEleve($eleve);
			$eleveatelier3->setAtelier($atelier3);
			$eleveatelier3->setQuestion($retour[$key][8]);
			$eleveatelier3->setStatus("0passage");
			$this->em->persist($eleveatelier3);
			$atelier3->setNbparticipant($atelier3->getNbparticipant()+1);
			$this->em->persist($atelier3);

			//Enregistrement en BDD
    		$this->em->flush();

    		//Ajout le poids aux ateliers
			$this->trie_atelier();
    		//POUR LA BARRE DE PROGRESSION
			$compteur ++;
			$session = new Session();
			$pourcent = round($compteur*100/$total);
			$session->set('progress',$pourcent);
			$session->set('compteur',$compteur);
			$session->save();
				
		}

		

		return $retour;
	}
	
	public function trie_atelier()
	{	
		$ateliers = $this->em->getRepository(Atelier::class)->findBy(
														array(),
														array('nbparticipant' => 'ASC')
		);
		foreach ($ateliers as $key => $atelier) {
			$atelier->setPoids(pow(10,$key));
			$this->em->persist($atelier);
		}
		$this->em->flush();

	}

	public function creer_groupe()
	{

		$groupes = $this->em->getRepository(Groupe::class)->findAll();
		if(count($groupes) == 0){
			 $ateliers = $this->em->getRepository(Atelier::class)->findAll();

			 //On trie les ateliers du plus demandé au moins demandé
			 foreach($ateliers as $key=>$atelier){
				$nombre[$key] = $atelier->getNbparticipant();
				$nbr_participants[$key] = floor($nombre[$key]/3)+1;	

				//on en profite pour créer les groupes
				$groupe1 = new Groupe;
				$groupe1->setAtelier($ateliers[$key]);
				$groupe1->setNum(1);
				$groupe1->setNbparticipant($nbr_participants[$key]);
				$groupe1->setNom("GROUPE 1 ".$ateliers[$key]->getNom());

				//on en profite pour créer les groupes
				$groupe2 = new Groupe;
				$groupe2->setAtelier($ateliers[$key]);
				$groupe2->setNum(2);
				$groupe2->setNbparticipant($nbr_participants[$key]);
				$groupe2->setNom("GROUPE 2 ".$ateliers[$key]->getNom());

				//on en profite pour créer les groupes
				$groupe3 = new Groupe;
				$groupe3->setAtelier($ateliers[$key]);
				$groupe3->setNum(3);
				$groupe3->setNbparticipant($nbr_participants[$key]);
				$groupe3->setNom("GROUPE 3 ".$ateliers[$key]->getNom());

				$this->em->persist($groupe1);
				$this->em->persist($groupe2);
				$this->em->persist($groupe3);
				$this->em->flush();
			 }
		}
		return true;

	}

}