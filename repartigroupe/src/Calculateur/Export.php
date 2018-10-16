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
			$membres =  $groupe->getEleveGroupes();
			$retour[$key]['membres'] = array();

			foreach($membres as $key2=>$membre)
			{
				$retour[$key]['membres'][] = $membre->getEleve()->getNom()." "
											.$membre->getEleve()->getPrenom()." "
											.$membre->getEleve()->getClasse()->getNom();
			}
		}

		return $retour;
	}

	public function csv()
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