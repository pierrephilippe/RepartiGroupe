<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityManager;

use App\Entity\GcuWeb;
use App\Form\GcuWebType;
use App\Services\ImportGcuWeb;

class SuperadminController extends AbstractController
{
  	private $nb_etape=3;

  	public function index()
	{
		$em = $this->getDoctrine()->getManager();
		$configuration = $em->getRepository(GcuWeb::class)->findAll();
		if(empty($configuration)){

			return $this->redirectToRoute('app_superadmin_config');
		}

		return $this->render('superadmin/index.html.twig', array('configuration' => $configuration)); 
	}

	public function config(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$configurations = $em->getRepository(GcuWeb::class)->findAll();
		if(!empty($configurations)){
			$configuration = $configurations[0];
		} else {
			// just setup a fresh $task object (remove the dummy data)
		    $configuration = new GcuWeb();
		}
        $form = $this->createForm(GcuWebType::class, $configuration);
        $form->handleRequest($request);
	
        if ($form->isSubmitted() && $form->isValid()) {
	        // $form->getData() holds the submitted values
	        // but, the original `$configuration` variable has also been updated
	        $configuration = $form->getData();

	        // ... perform some action, such as saving the configuration to the database
	        // for example, if Task is a Doctrine entity, save it!
	        $entityManager = $this->getDoctrine()->getManager();
	        $entityManager->persist($configuration);
	        $entityManager->flush();

	        return $this->redirectToRoute('app_superadmin_home');
	    }
	    return $this->render('superadmin/config.html.twig', array(
	        'form' => $form->createView(),
	    ));
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
		return $this->render('superadmin/etapes.html.twig',
        	array(
	        	'etape' => 1,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 1",
	        	'url_action' => 'app_superadmin_etape1',
        		'url_suivant' => 'app_superadmin_etape2'
        	)
        );  
	}

	public function etape2(Request $request, ImportGcuWeb $importgcuweb)
	{
		if ($request->isMethod('POST')){
			//service ImportGcuWeb
			$fichier_a_importer = $importgcuweb->importcsv();
			//dump($fichier_a_importer);
			//die();
			return new Response ("Chargement terminé ");
		}
		return $this->render('superadmin/etapes.html.twig',
        	array(
	        	'etape' => 2,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 2",
	        	//'url_action' => 'app_superadmin_etape2',
        		//'url_suivant' => 'app_superadmin_etape3'
        	)
        );  
	}

	public function etape3(Request $request, ImportGcuWeb $importgcuweb)
	{
		return $this->render('superadmin/etapes.html.twig',
        	array(
	        	'etape' => 3,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 3"
        	)
        );  
	}
}