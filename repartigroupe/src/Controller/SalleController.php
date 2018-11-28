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
use App\Entity\Salle;
use App\Form\SalleType;
use App\Entity\Intervenant;
use App\Entity\EleveSalle;
use App\Entity\Groupe;

class SalleController extends AbstractController
{
	public function salles()
	{
		$em = $this->getDoctrine()->getManager();
		$salles = $em->getRepository(Salle::class)->findAll();
		return $this->render('admin/parametrage/salles.html.twig', 
			array('salles' => $salles)); 
	}

	public function salles_ajoute(Request $request)
	{
		$salle = new Salle();
		
    	$form   = $this->get('form.factory')->create(SalleType::class, $salle, array(
    			'action' => $this->generateUrl('app_admin_parametrage_salles_ajoute'),
    			'method' => 'POST'
    		));
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      		$em = $this->getDoctrine()->getManager();
      		$em->persist($salle);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Salle bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_salles');
    	}
    	return $this->render('admin/parametrage/salles_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}
	
	public function salles_modifie(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$salle = $em->getRepository(Salle::class)->findOneById($id);
		if(!$salle){
			return $this->redirectToRoute('app_admin_parametrage_salles');
		}
    	$form   = $this->get('form.factory')->create(SalleType::class, $salle, 
    		array(
    			'action' => $this->generateUrl('app_admin_parametrage_salles_modifie', 
    											array('id'=>$id)),
    			'method' => 'POST'
    		)
    	);
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      		$em = $this->getDoctrine()->getManager();
      		$em->persist($salle);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Salle bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_salles');
    	}
    	return $this->render('admin/parametrage/salles_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}

	public function salles_supprime($id)
	{
		$em = $this->getDoctrine()->getManager();
		$salle = $em->getRepository(Salle::class)->findOneById($id);
		if(!$salle){
			return $this->redirectToRoute('app_admin_parametrage_salles');
		}
		//encore des groupes attachés a cet salle ?
		$groupe = $em->getRepository(Groupe::class)->findBySalle($salle);
		
		if(!$groupe){
			$em->remove($salle);
			$em->flush();
			return new Response(
			    'Content',
			    Response::HTTP_OK,
			    array('content-type' => 'text/html')
			);
		} 
		return new Response(
			    'Content',
			    Response::HTTP_NOT_MODIFIED,
			    array('content-type' => 'text/html')
		);		
	}
}