<?php 
namespace App\Services;

use Doctrine\ORM\EntityManager;

use App\Entity\Inscription;


class Autorisations
{
    private $em;

    public function __construct(EntityManager $em) { //Son constructeur avec l'entity manager en paramètre
    	$this->em = $em;
  	}

    public function parametrage()
    {
        //VRAI SI PAS D'INSCRIPTION EN COURS
        $autorise = false;
		$inscriptions = $this->em->getRepository(Inscription::class)->findAll();
		if(empty($inscriptions)){
			$autorise = true;
		} 

		return $autorise;
    }

}