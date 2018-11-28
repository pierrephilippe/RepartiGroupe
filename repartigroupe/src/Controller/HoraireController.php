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
use App\Entity\Horaire;
use App\Form\HoraireType;
use App\Entity\Intervenant;
use App\Entity\EleveHoraire;
use App\Entity\Groupe;

class HoraireController extends AbstractController
{
	public function horaires()
	{
		$em = $this->getDoctrine()->getManager();
		$horaires = $em->getRepository(Horaire::class)->findAll();
		return $this->render('admin/parametrage/horaires.html.twig', 
			array('horaires' => $horaires)); 
	}

	public function horaires_ajoute(Request $request)
	{
		$horaire = new Horaire();
		
    	$form   = $this->get('form.factory')->create(HoraireType::class, $horaire, array(
    			'action' => $this->generateUrl('app_admin_parametrage_horaires_ajoute'),
    			'method' => 'POST'
    		));
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      		$em = $this->getDoctrine()->getManager();
      		$em->persist($horaire);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Horaire bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_horaires');
    	}
    	return $this->render('admin/parametrage/horaires_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}
	
	public function horaires_modifie(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$horaire = $em->getRepository(Horaire::class)->findOneById($id);
		if(!$horaire){
			return $this->redirectToRoute('app_admin_parametrage_horaires');
		}
    	$form   = $this->get('form.factory')->create(HoraireType::class, $horaire, 
    		array(
    			'action' => $this->generateUrl('app_admin_parametrage_horaires_modifie', 
    											array('id'=>$id)),
    			'method' => 'POST'
    		)
    	);
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      		$em = $this->getDoctrine()->getManager();
      		$em->persist($horaire);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Horaire bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_horaires');
    	}
    	return $this->render('admin/parametrage/horaires_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}

	public function horaires_supprime($id)
	{
		$em = $this->getDoctrine()->getManager();
		$horaire = $em->getRepository(Horaire::class)->findOneById($id);
		if(!$horaire){
			return $this->redirectToRoute('app_admin_parametrage_horaires');
		}
		//encore des groupes attachés a cet horaire ?
		$groupe = $em->getRepository(Groupe::class)->findByHoraire($horaire);
		
		if(!$groupe){
			$em->remove($horaire);
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