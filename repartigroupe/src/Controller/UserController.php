<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

use App\Entity\Eleve;
use App\Entity\EleveAtelier;
use App\Services\Autorisations;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function accueil(Autorisations $autorisations)
    {
        $em = $this->getDoctrine()->getManager();
		$autorise = $autorisations->parametrage(); 
		
		$utilisateur = $this->getUser();

		$message = "";

		if($autorisations->remplirformulaireeleve()){
			$eleve = $em->getRepository(Eleve::class)->findOneByUser($this->getUser());
			$elevesateliers = $em->getRepository(EleveAtelier::class)->findByEleve($eleve);
			//est ce que cet élève à répondu à l'enquete ?
			if(count($elevesateliers) > 0){
				$message .= " a déjà répondu !";
			} else {
				$message .= " peut remplir";
			}

		} else {
			$message .= "Pas autorisé à remplir";
		}
        return $this->render('user/index.html.twig', array('utilisateur' => $utilisateur,
        												   'message'=>$message
        												));
    }
}
