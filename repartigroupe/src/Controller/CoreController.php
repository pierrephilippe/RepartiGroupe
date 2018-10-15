<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\SplFileInfo;

use App\Googleform\Import;
use App\Calculateur\Groupe;

use League\Csv\Reader;
use League\Csv\Statement;

use App\Entity\EleveAtelier;

class CoreController extends AbstractController
{
    public function index()
    {
        return $this->render('index.html.twig', [
            'name' => "toto"
        ]);
    }

    public function lirecsv(Import $import)
    {

		
		//On détermine quel fichier il faut importer

		$nb_fichier = 0;			//pour vérifier qu'il y a un seul CSV		
		$path = "csv/";				//dans le dossier public/csv/
		
		if($dossier = opendir($path))
		{
			while(false !== ($fichier = readdir($dossier)))
			{	
				$info = new SplFileInfo($fichier, $path, $path);
				if((strcmp($info->getExtension(),"csv") == 0) && $nb_fichier == 0)
				{
					$nb_fichier++;
					$fichier_a_importer = $path.$fichier;
				}
			}
		}

		

		//On importer le seul fichier CSV trouvé
		if($nb_fichier == 1){
			//Appel du service 
			$contenucsv = $import->csv($fichier_a_importer);

		}
        return $this->render('csv/lire.html.twig',
        	array(  'contenucsv' => $contenucsv)
        );   
    }

    public function calculgroupe(Groupe $groupe)
    {
        /*
         * On récupère tous les champs EleveAtelier dans un tableau
         */
        $retour = $groupe->calcul();
        return $this->render('calculgroupe.html.twig',
    		array('retour' => $retour)
    	);
    }
}