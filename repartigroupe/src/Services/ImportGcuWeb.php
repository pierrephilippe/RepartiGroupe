<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

use Doctrine\ORM\EntityManager;

use League\Csv\Reader;
use League\Csv\Statement;

use App\Entity\GcuWeb;
use App\Entity\User;

class ImportGcuWeb
{
	private $em;
	private $destination = "csvgcuweb";
	private $passwordEncoder;

    
  	public function __construct(EntityManager $em, 
  								UserPasswordEncoderInterface $passwordEncoder) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
    	$this->passwordEncoder = $passwordEncoder;
  	}

	public function getcsv()
	{
		$configurations = $this->em->getRepository(GcuWeb::class)->findAll();

		if(empty($configurations)){
			return false;
		}
		//securisation dossier destination
	    if(!is_dir($this->destination))
	        mkdir($this->destination, 0750);
	    if(!is_file($this->destination.'/.htaccess')){
	        //if(strcmp(file_get_contents($destination.'/.htaccess'),'deny from all')){  
	            $htaccess = 'deny from all';
	            $securise = file_put_contents( $this->destination.'/.htaccess', $htaccess );  
	            if(!$securise)
	                throw new \Exception("Impossible de sécuriser le dossier de destination : ".$this->destination);
	    }      

	    //récupération du fichier CSV
	    $urlcsv = "https://".$configurations[0]->getLogin().
	    					":".$configurations[0]->getPass().
	    					"@".$configurations[0]->getUrl().
	    					$configurations[0]->getFichier();
	    $content = @file_get_contents($urlcsv);
	    if($content){
	        //copie du fichier en local
	        file_put_contents($this->destination."/".$configurations[0]->getFichier(), $content);
	        return true;
	    } else {
	        $request->getSession()->getFlashBag()->add('danger', 'Erreur avec en essayant de récupérer le "'.$fichier.'". Veuillez vérifier la configuration.');
	        throw new \Exception("Impossible de récupérer le fichier CSV");
	    }



	}
	
	public function importcsv()
	{
		ini_set('max_execution_time', -1);

		//Récupération du nom de fichier : 

		$configurations = $this->em->getRepository(GcuWeb::class)->findAll();
		if(empty($configurations)){
			return false;
		}
		$fichier = $this->destination."/".$configurations[0]->getFichier();

		//on vide la table utilisateur pour les utilisateur ayant le role USER.
		$users = $this->em->getRepository(User::class)->findByRole('ROLE_USER');
		foreach($users as $user){
			$this->em->remove($user);
		}
        $this->em->flush();


		//on parcourt le CSV et import chauqe ligne
		$csv = Reader::createFromPath($fichier, 'r');
		$csv->setDelimiter(';');
		
		//initialisation barre de progression
		$compteur = 0;
		$percent = 0;
		$total = count(file($fichier));

		//on ignore la premiere ligne
		$stmt = (new Statement())
		    ->offset(1)
		;

		$csv = $stmt->process($csv);

		foreach ($csv as $record) {

           if(strstr(trim($record[6]), "Activ") && substr(trim($record[7]),0,1) == "3"){
	            
	            $user = new User();
	            $user->setNom($record[1]);
	            $user->setPrenom($record[2]);
	            $user->setUsername($record[3]);
	            $user->setClasse($record[7]);
	            $user->setPassword($this->passwordEncoder->encodePassword($user, $record[4]));
	            //$user->setPassword($record[4]);
	            //$user->setEmail($email);
	            $user->setRoles(['ROLE_USER']);
	            $this->em->persist($user);
	            $this->em->flush();
	        }    
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
