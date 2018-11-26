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
use App\Entity\GcuWeb;
use App\Form\GcuWebType;
use App\Services\ImportGcuWeb;

class AdminController extends AbstractController
{
	private $nb_etape=3;

	public function index()
	{
		$em = $this->getDoctrine()->getManager();

		$utilisateurs = $em->getRepository(User::class)->findByRole('ROLE_USER');
		$ateliers = $em->getRepository(Atelier::class)->findAll();
		return $this->render('admin/index.html.twig', 
			array('utilisateurs' => $utilisateurs,
				  'ateliers' => $ateliers)); 
	}

	public function atelier()
	{
		$em = $this->getDoctrine()->getManager();

		$ateliers = $em->getRepository(Atelier::class)->findAll();
		return $this->render('admin/ateliers.html.twig', 
			array('ateliers' => $ateliers)); 
	}


		public function etape1(Request $request, ImportGcuWeb $importgcuweb)
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
			return new Response ("Chargement terminé ");
		}
		return $this->render('admin/import-eleves.html.twig',
        	array(
	        	'etape' => 1,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 1",
	        	'url_action' => 'app_admin_etape1',
        		'url_suivant' => 'app_admin_etape2'
        	)
        );  
	}

	public function etape2(Request $request, ImportGcuWeb $importgcuweb)
	{
		if ($request->isMethod('POST')){
			//service ImportGcuWeb
			$fichier_a_importer = $importgcuweb->importcsv();
			return new Response ("Chargement terminé ");
		}
		return $this->render('admin/import-eleves.html.twig',
        	array(
	        	'etape' => 2,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 2",
	        	'url_action' => 'app_admin_etape2',
        		'url_suivant' => 'app_admin_etape3'
        	)
        );  
	}

	public function etape3(Request $request, ImportGcuWeb $importgcuweb)
	{
		return $this->render('admin/import-eleves.html.twig',
        	array(
	        	'etape' => 3,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 3"
        	)
        );  
	}
}