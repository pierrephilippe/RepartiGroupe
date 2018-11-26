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
  	

  	public function index()
	{
		$em = $this->getDoctrine()->getManager();
		$configuration = $em->getRepository(GcuWeb::class)->findAll();
		if(empty($configuration)){

			return $this->redirectToRoute('app_superadmin_config');
		}

		return $this->render('superadmin/index.html.twig'); 
	}

	public function config(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$configurations = $em->getRepository(GcuWeb::class)->findAll();
		if(!empty($configurations)){
			//1 seule configuration possible.
			$configuration = $configurations[0];
		} else {
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
}