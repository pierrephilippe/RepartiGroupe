<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;
use App\Entity\GcuWeb;


class ImportGcuWeb
{
	private $em;

  	public function __construct(EntityManager $em) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
  	}

	public function getcsv()
	{
		$configurations = $this->em->getRepository(GcuWeb::class)->findAll();

		if(empty($configurations)){
			return false;
		}


		$destination = "csvgcuweb";

		//securisation dossier destination
	    if(!is_dir($destination))
	        mkdir($destination, 0750);
	    if(!is_file($destination.'/.htaccess')){
	        //if(strcmp(file_get_contents($destination.'/.htaccess'),'deny from all')){  
	            $htaccess = 'deny from all';
	            $securise = file_put_contents( $destination.'/.htaccess', $htaccess );  
	            if(!$securise)
	                throw new \Exception("Impossible de sécuriser le dossier de destination : ".$destination);
	    }      

	    //récupération du fichier CSV
	    $urlcsv = "https://".$configurations[0]->getLogin().
	    					":".$configurations[0]->getPass().
	    					"@".$configurations[0]->getUrl().
	    					$configurations[0]->getFichier();
	    $content = @file_get_contents($urlcsv);
	    if($content){
	        //copie du fichier en local
	        file_put_contents($destination."/".$configurations[0]->getFichier(), $content);
	        return true;
	    } else {
	        $request->getSession()->getFlashBag()->add('danger', 'Erreur avec en essayant de récupérer le "'.$fichier.'". Veuillez vérifier la configuration.');
	        throw new \Exception("Impossible de récupérer le fichier CSV");
	    }



	}
	
	public function importcsv()
	{
	}


}
