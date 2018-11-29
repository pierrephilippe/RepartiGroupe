<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Inscription;
use App\Entity\User;
use App\Entity\Eleve;
use App\Entity\Classe;
use App\Entity\EleveAtelier;

class InscriptionController extends AbstractController
{
    
    public function index(Request $request){
    	$em = $this->getDoctrine()->getManager();
    	$inscriptions = $em->getRepository(Inscription::class)->findAll();
    	if(empty($inscriptions)){
			//aucune inscription commencée
			return $this->render('admin/inscriptions/index.html.twig');
    	} else {

    		// Cas ou les inscriptions sont déjà ouvertes
			if($inscriptions[0]->getStatus() == "encours"){
				return $this->redirectToRoute('app_admin_inscriptions_start');
			}

    	}
        return $this->render('admin/inscriptions/index.html.twig');
    }

    public function start(Request $request){
    	$em = $this->getDoctrine()->getManager();

    	$inscriptions = $em->getRepository(Inscription::class)->findAll();
    	if(!empty($inscriptions)){
			if($inscriptions[0]->getStatus() == "encours"){
				$inscription = $inscriptions[0];
			} 
    	} else {
			$inscription = new Inscription();
			$inscription->setDate(new \DateTime());
			$inscription->setStatus("encours");
			$em->persist($inscription);
			$em->flush($inscription);
			//copie des utilisateurs USER dans table Eleves
			$users = $em->getRepository(User::class)->findByRole('ROLE_USER');
			foreach($users as $user){
				$eleve = $em->getRepository(Eleve::class)->findOneByUser($user);
				
				if(!$eleve){
					$eleve = new Eleve();
					$eleve->setNom($user->getNom());
					$eleve->setPrenom($user->getPrenom());
					$classe=$em->getRepository(Classe::class)->findOneByNom($user->getClasse());
					if($classe){
						$eleve->setClasse($classe);
					}
					$eleve->setUser($user);
					$em->persist($eleve);
					$em->flush($eleve);
				}

			}
		}
		
		$eleves = $em->getRepository(Eleve::class)->findAll();
		$eleves_ateliers = $em->getRepository(EleveAtelier::class)->findAll();

		//eleve ayant répondu à l'enquête
		$repondus = $em->getRepository(Eleve::class)->findGroupeByEleve();

		//eleve n'ayant pas répondu à l'enquete
		$nonrepondus = $em->getRepository(Eleve::class)->findNonInscritGroupeByEleve();

        return $this->render('admin/inscriptions/ouvert.html.twig', array('inscription'=>$inscription,
    																	  'eleves' => $eleves, 
    																	  'eleves_ateliers' => $eleves_ateliers, 
    																	  'repondus' => $repondus,
    																	  'nonrepondus' => $nonrepondus));
    }

}