<?php

namespace App\Calculateur;

use Doctrine\ORM\EntityManager;
use League\Csv\Reader;
use League\Csv\Statement;

use App\Entity\Eleve;
use App\Entity\Classe;
use App\Entity\Atelier;
use App\Entity\EleveAtelier;
use App\Entity\Groupe;
use App\Entity\EleveGroupe;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Export
{
	private $em;

  	public function __construct(EntityManager $em) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
  	}

	public function tableau()
	{
		$retour = array();

		$groupes = $this->em->getRepository(Groupe::class)->findAll();

		foreach($groupes as $key=>$groupe)
		{
			$retour[$key]['nom_groupe'] = $groupe->getNom();
			$retour[$key]['num_groupe'] = (int)substr($groupe->getNom(),7,1);
			$retour[$key]['nom_atelier'] = $groupe->getAtelier()->getNom();
			$retour[$key]['num_atelier'] = (int)substr($groupe->getAtelier()->getNom(),6,3);
			$membres =  $groupe->getEleveGroupes();
			$retour[$key]['membres'] = array();
			
			foreach($membres as $key2=>$membre)
			{
				$retour[$key]['membres'][] = $membre->getEleve()->getNom()." "
											.$membre->getEleve()->getPrenom()." "
											.$membre->getEleve()->getClasse()->getNom();
			}
		}

		//juste pour le tri
		foreach ($retour as $key => $row) {
		    $num_groupe[$key]  = $row['num_groupe'];
		    $num_atelier[$key] = $row['num_atelier'];
		}
		$num_groupe  = array_column($retour, 'num_groupe');
		$num_atelier = array_column($retour, 'num_atelier');
		array_multisort($num_atelier, SORT_ASC,
						$num_groupe, SORT_ASC,
						$retour);
		
		return $retour;
	}

	public function csv_secretariat()
	{
		$retour = array();

		$eleves = $this->em->getRepository(Eleve::class)->findAll();


		$fileName = "export_" . date("d_m_Y") . ".csv";
        $response = new StreamedResponse();
 
 
        $response->setCallback(function() use ($eleves){
            $handle = fopen('php://output', 'w+');
 			fputs($handle, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF)));
 			$values = [
				"NOM",
				"PRENOM",
				"CLASSE",
				"GROUPE 1",
				"QUESTION 1",
				"GROUPE 2",
				"QUESTION 2",
				"GROUPE 3",
				"QUESTION 3\n"
 			];
 			fwrite($handle, implode(';', $values));
            foreach ($eleves as $eleve)
            {
            	$values = [
					$eleve->getNom(),
					$eleve->getPrenom(),
					$eleve->getClasse()->getNom()
            	];
            	$les_choix = $eleve->getEleveGroupes();
            	foreach ($les_choix as $un_choix)
            	{
					$values[] = $un_choix->getGroupe()->getNom();
					$values[] = $un_choix->getQuestion();
            	}
            	$values[] = "\n";
				

            	fwrite($handle, implode(';', $values));
            }
            fclose($handle);
        });
 
 
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8', 'application/force-download');
        $response->headers->set('Content-Disposition','attachment; filename='.$fileName);
 
        
        return $response;
	}
}