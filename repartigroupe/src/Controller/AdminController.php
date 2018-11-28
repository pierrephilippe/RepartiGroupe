<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use App\Entity\User;
use App\Entity\Atelier;
use App\Form\AtelierType;
use App\Entity\Intervenant;
use App\Entity\Salle;
use App\Entity\Horaire;
use App\Entity\Eleve;
use App\Entity\EleveGroupe;
use App\Entity\EleveAtelier;
use App\Entity\Groupe;
use App\Entity\GcuWeb;
use App\Entity\Inscription;
use App\Form\GcuWebType;
use App\Services\ImportGcuWeb;
use App\Services\Autorisations;

class AdminController extends AbstractController
{
	private $nb_etape=4;

	public function accueil()
	{
		$em = $this->getDoctrine()->getManager();

		$utilisateurs = $em->getRepository(User::class)->findByRole('ROLE_USER');
		$ateliers = $em->getRepository(Atelier::class)->findAll();
		return $this->render('admin/index.html.twig', 
			array('utilisateurs' => $utilisateurs,
				  'ateliers' => $ateliers)); 
	}

	public function initialisation()
	{
		$em = $this->getDoctrine()->getManager();
		$groupes = count($em->getRepository(Groupe::class)->findAll());
		$eleves = count($em->getRepository(User::class)->findByRole('ROLE_USER'));
		$ateliers = count($em->getRepository(Atelier::class)->findAll());
		$intervenants = count($em->getRepository(Intervenant::class)->findAll());
		$salles = count($em->getRepository(Salle::class)->findAll());
		$horaires = count($em->getRepository(Horaire::class)->findAll());
		return $this->render('admin/initialisation/initialisation.html.twig', array('groupes' => $groupes,
																	 'eleves' => $eleves,
																	 'ateliers' => $ateliers,
																	 'intervenants' => $intervenants,
																	 'salles' => $salles,
																	 'horaires' => $horaires)); 
	}

	public function reinitialisation()
	{
		$em = $this->getDoctrine()->getManager();
		$eleves = $em->getRepository(Eleve::class)->findAll();
		foreach($eleves as $eleve){
			$em->remove($eleve);
		}
		$elevesgroupes = $em->getRepository(EleveGroupe::class)->findAll();
		foreach($elevesgroupes as $elevegroupe){
			$em->remove($elevegroupe);
		}
		$elevesateliers = $em->getRepository(EleveAtelier::class)->findAll();
		foreach($elevesateliers as $eleveatelier){
			$em->remove($eleveatelier);
		}
		$groupes = $em->getRepository(Groupe::class)->findAll();
		foreach($groupes as $groupe){
			$em->remove($groupe);
		}
		$users = $em->getRepository(User::class)->findByRole('ROLE_USER');
		foreach($users as $user){
			$em->remove($user);
		}
		$inscriptions = $em->getRepository(Inscription::class)->findAll();
		foreach($inscriptions as $inscription){
			$em->remove($inscription);
		}
		$em->flush();
		return $this->redirectToRoute('app_admin_initialisation'); 
	}
	
	public function eleves(Request $request, ImportGcuWeb $importgcuweb,  Autorisations $autorisations)
	{
		
		$em = $this->getDoctrine()->getManager();		
		$eleves = $em->getRepository(User::class)->findByRole('ROLE_USER');
		if(!$eleves){
			return $this->redirectToRoute('app_admin_parametrage_eleves_etape1'); 
		} 

		$autorise = $autorisations->parametrage();

		return $this->render('admin/parametrage/eleves.html.twig',
        	array(
	        	'eleves' => $eleves,
	        	'autorise' => $autorise
        	)
        );  
	}

	public function etape1(Request $request, ImportGcuWeb $importgcuweb)
	{
		
		$em = $this->getDoctrine()->getManager();
		$configuration = $em->getRepository(GcuWeb::class)->findAll();
		if(empty($configuration)){
			return $this->redirectToRoute('app_superadmin_config');
		}
		
		$eleves = $em->getRepository(User::class)->findByRole('ROLE_USER');
	
		return $this->render('admin/parametrage/import-eleves.html.twig',
        	array(
	        	'etape' => 1,
	        	'nb_etape' => $this->nb_etape,
	        	'eleves' => $eleves,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 1",
        		'url_suivant' => 'app_admin_parametrage_eleves_etape2'
        	)
        );  
	}

	public function etape2(Request $request, ImportGcuWeb $importgcuweb)
	{
		
		$em = $this->getDoctrine()->getManager();
		$configuration = $em->getRepository(GcuWeb::class)->findAll();
		if(empty($configuration)){
			return $this->redirectToRoute('app_superadmin_config');
		}

		if ($request->isMethod('POST')){
			//service ImportGcuWeb
			$fichier_a_importer = $importgcuweb->getcsv();
			//dump($fichier_a_importer);
			//die();
			return new Response("Chargement terminé ");
		} 
		return $this->render('admin/parametrage/import-eleves.html.twig',
        	array(
	        	'etape' => 2,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 2",
	        	'url_action' => 'app_admin_parametrage_eleves_etape2',
        		'url_suivant' => 'app_admin_parametrage_eleves_etape3'
        	)
        );  
	}

	public function etape3(Request $request, ImportGcuWeb $importgcuweb)
	{
		if ($request->isMethod('POST')){
			//service ImportGcuWeb
			$fichier_a_importer = $importgcuweb->importcsv();
			return new Response ("Chargement terminé ");
		}
		return $this->render('admin/parametrage/import-eleves.html.twig',
        	array(
	        	'etape' => 3,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 3",
	        	'url_action' => 'app_admin_parametrage_eleves_etape3',
        		'url_suivant' => 'app_admin_parametrage_eleves_etape4'
        	)
        );  
	}

	public function etape4(Request $request, ImportGcuWeb $importgcuweb)
	{
		return $this->render('admin/parametrage/import-eleves.html.twig',
        	array(
	        	'etape' => 4,
	        	'nb_etape' => $this->nb_etape,
	        	'titre_etape' => "Etape 4"
        	)
        );  
	}
}