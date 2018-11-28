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
use App\Entity\Intervenant;
use App\Form\IntervenantType;
use App\Entity\EleveIntervenant;
use App\Entity\Groupe;

class IntervenantController extends AbstractController
{
	public function intervenants()
	{
		$em = $this->getDoctrine()->getManager();
		$intervenants = $em->getRepository(Intervenant::class)->findAll();
		return $this->render('admin/parametrage/intervenants.html.twig', 
			array('intervenants' => $intervenants)); 
	}

	public function intervenants_ajoute(Request $request)
	{
		$intervenant = new Intervenant();
		
    	$form   = $this->get('form.factory')->create(IntervenantType::class, $intervenant, array(
    			'action' => $this->generateUrl('app_admin_parametrage_intervenants_ajoute'),
    			'method' => 'POST'
    		));
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      		$em = $this->getDoctrine()->getManager();
      		$em->persist($intervenant);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Intervenant bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_intervenants');
    	}
    	return $this->render('admin/parametrage/intervenants_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}
	
	public function intervenants_modifie(Request $request, $id)
	{
		$em = $this->getDoctrine()->getManager();
		$intervenant = $em->getRepository(Intervenant::class)->findOneById($id);
		if(!$intervenant){
			return $this->redirectToRoute('app_admin_parametrage_intervenants');
		}
    	$form   = $this->get('form.factory')->create(IntervenantType::class, $intervenant, 
    		array(
    			'action' => $this->generateUrl('app_admin_parametrage_intervenants_modifie', 
    											array('id'=>$id)),
    			'method' => 'POST'
    		)
    	);
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      		$em = $this->getDoctrine()->getManager();
      		$em->persist($intervenant);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Intervenant bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_intervenants');
    	}
    	return $this->render('admin/parametrage/intervenants_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}

	public function intervenants_supprime($id)
	{
		$em = $this->getDoctrine()->getManager();
		$intervenant = $em->getRepository(Intervenant::class)->findOneById($id);
		if(!$intervenant){
			return $this->redirectToRoute('app_admin_parametrage_intervenants');
		}
		//encore des groupes attachés a cet intervenant ?
		$groupe = $em->getRepository(Groupe::class)->findByIntervenant($intervenant);
		
		if(!$groupe){
			$em->remove($intervenant);
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