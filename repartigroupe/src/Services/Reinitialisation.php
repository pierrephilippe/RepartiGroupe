<?php

namespace App\Services;

use Doctrine\ORM\EntityManager;


class Reinitialisation
{
	private $em;

  	public function __construct(EntityManager $em) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
  	}

	public function effacecsv($repertoire)
	{
		try {
				$dossier = opendir($repertoire);
				while($fichier = readdir($dossier)) {
					if($fichier != "." && 
						$fichier != ".." && 
						$fichier != ".DS_Store" && 
						$fichier != ".gitignore" && 
						$fichier != ".htaccess"){
						
						unlink($repertoire."/".$fichier);

					}
				}
				closedir($dossier);
				return true;

		 } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                new \Exception("Erreur en vidant le répertoire ".$repertoire);
                return false;
         }
	}

	public function effacebdd()
	{
		$connection = $this->em->getConnection();
		$platform   = $connection->getDatabasePlatform();
  		$connection->query('SET FOREIGN_KEY_CHECKS=0');
  		$connection->executeUpdate($platform->getTruncateTableSQL('eleve_atelier', false));
  		$connection->executeUpdate($platform->getTruncateTableSQL('eleve_groupe', false));
		$connection->executeUpdate($platform->getTruncateTableSQL('atelier', false));
		$connection->executeUpdate($platform->getTruncateTableSQL('classe', false));
		$connection->executeUpdate($platform->getTruncateTableSQL('eleve', false));
		$connection->executeUpdate($platform->getTruncateTableSQL('groupe', false));
		$connection->executeUpdate($platform->getTruncateTableSQL('intervenant', false));
		$connection->executeUpdate($platform->getTruncateTableSQL('salle', false));
		$connection->query('SET FOREIGN_KEY_CHECKS=1');
	}
}
