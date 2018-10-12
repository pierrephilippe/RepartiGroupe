<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use League\Csv\Reader;
use League\Csv\Statement;


class CoreController extends AbstractController
{
    public function index()
    {
        return $this->render('index.html.twig', [
            'name' => "toto"
        ]);
    }

    public function lirecsv()
    {
        
        $nb_fichier=0;
        $listefichiers[] = array();

        if($dossier = opendir('csv'))
		{	
	        while(false !== ($fichier = readdir($dossier)))
			{
				if($fichier != '.' && $fichier != '..' && $fichier != '.DS_Store')
				{
					
					$listefichiers[$nb_fichier] = $fichier;
					$nb_fichier++; // On incrémente le compteur de 1
				} // On ferme le if (qui permet de ne pas afficher index.php, etc.)
				
			}

			if($nb_fichier == 1){
			    // Dump the absolute path
			    $csv = Reader::createFromPath("csv/".$listefichiers[0], 'r');
				$csv->setHeaderOffset(0); //set the CSV header offset

				foreach ($csv as $index => $row) {
				    
				}
			}

	        return $this->render('csv/lire.html.twig', [
	            'listefichiers' => $listefichiers,
	            'lignes' => $lignes
	        ]);
	    } else {
			throw new \Exception("Impossible de trouver le dossier CSV");
	    }
    }
}