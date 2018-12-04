<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;

use App\Entity\Eleve;
use App\Entity\EleveAtelier;
use App\Entity\Inscription;
use App\Services\Autorisations;

use App\Form\EleveAtelierType;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function accueil(Request $request, Autorisations $autorisations)
    {
        $em = $this->getDoctrine()->getManager();
		$autorise = $autorisations->parametrage(); 
		
		$utilisateur = $this->getUser();

		$message = "";

		if($autorisations->remplirformulaireeleve()){
			$eleve = $em->getRepository(Eleve::class)->findOneByUser($this->getUser());
			$elevesateliers = $em->getRepository(EleveAtelier::class)->findByEleve($eleve);
			
			$ateliers = new ArrayCollection();
			foreach($elevesateliers as $ea){
				$ateliers->add($ea->getAtelier());
			}

			if(count($elevesateliers) > 2){
				$message .= " a déjà tout répondu !";
			} else {
				$num = count($elevesateliers)+1;
				$message = "Choix Atelier n°".$num;
				$eleveatelier = new EleveAtelier();
				$eleveatelier->setEleve($eleve);
				$eleveatelier->setStatus("");

				$form = $this->createForm(EleveAtelierType::class, $eleveatelier, array('ateliers' => $ateliers));
        		$form->handleRequest($request);

        		if ($form->isSubmitted() && $form->isValid()) {
			        $eleveatelier = $form->getData();
			        $em->persist($eleveatelier);
			        $em->flush();

			        return $this->redirectToRoute('app_user_accueil');
			    }

				return $this->render('user/index.html.twig', array('utilisateur' => $utilisateur,
																   'message' => $message,
        												   		   'form' => $form->createView(),
        															));
				
			}

		} else {
			$message .= "Pas autorisé à remplir";
		}
        return $this->render('user/index.html.twig', array('utilisateur' => $utilisateur,
        												   'message'=>$message
        												));
    }
}
