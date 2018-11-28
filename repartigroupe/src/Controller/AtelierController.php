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
use App\Entity\Eleve;
use App\Entity\EleveGroupe;
use App\Entity\EleveAtelier;
use App\Entity\Groupe;

use App\Services\Autorisations;

class AtelierController extends AbstractController
{
	public function ateliers(Autorisations $autorisations)
	{
		$em = $this->getDoctrine()->getManager();
		$ateliers = $em->getRepository(Atelier::class)->findAll();
		$autorise = $autorisations->parametrage(); 
		return $this->render('admin/parametrage/ateliers.html.twig', 
			array('ateliers' => $ateliers, 
				  'autorise' => $autorise)); 
	}

	public function ateliers_ajoute(Request $request, Autorisations $autorisations)
	{
		$autorise = $autorisations->parametrage();
		if(!$autorise)
			throw new \Exception("Inscriptions en cours, non autorisé");


		$atelier = new Atelier();
		$em = $this->getDoctrine()->getManager();
		$atelier->setNumero(count($em->getRepository(Atelier::class)->findAll()) + 1);

    	$form   = $this->get('form.factory')->create(AtelierType::class, $atelier, array(
    			'action' => $this->generateUrl('app_admin_parametrage_ateliers_ajoute'),
    			'method' => 'POST'
    		));
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
			$atelier->setNom("Thème ".$atelier->getNumero().": ".$atelier->getTitre()); 
			$atelier->setNbparticipant(0);
      		$em->persist($atelier);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Atelier bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_ateliers');
    	}
    	return $this->render('admin/parametrage/ateliers_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}
	
	public function ateliers_modifie(Request $request, $id, Autorisations $autorisations)
	{
		$autorise = $autorisations->parametrage();
		if(!$autorise)
			throw new \Exception("Inscriptions en cours, non autorisé");

		$em = $this->getDoctrine()->getManager();
		$atelier = $em->getRepository(Atelier::class)->findOneById($id);
		if(!$atelier){
			return $this->redirectToRoute('app_admin_parametrage_ateliers');
		}
    	$form   = $this->get('form.factory')->create(AtelierType::class, $atelier, 
    		array(
    			'action' => $this->generateUrl('app_admin_parametrage_ateliers_modifie', 
    											array('id'=>$id)),
    			'method' => 'POST'
    		)
    	);
    	if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
      		$em = $this->getDoctrine()->getManager();
      		$atelier->setNom("Thème ".$atelier->getNumero().": ".$atelier->getTitre());
      		$em->persist($atelier);
      		$em->flush();
			
      		$request->getSession()->getFlashBag()->add('notice', 'Atelier bien enregistrée.');

      		return $this->redirectToRoute('app_admin_parametrage_ateliers');
    	}
    	return $this->render('admin/parametrage/ateliers_form.html.twig', array(
      						'form' => $form->createView(),
    	));
	}

	public function ateliers_supprime($id, Autorisations $autorisations)
	{
		$autorise = $autorisations->parametrage();
		if(!$autorise)
			throw new \Exception("Inscriptions en cours, non autorisé");

		$em = $this->getDoctrine()->getManager();
		$atelier = $em->getRepository(Atelier::class)->findOneById($id);
		if(!$atelier){
			return $this->redirectToRoute('app_admin_parametrage_ateliers');
		}
		//encore des groupes attachés a cet atelier ?
		$groupe = $em->getRepository(Groupe::class)->findByAtelier($atelier);

		//encore des demandes attachés a cet atelier ?
		$eleve_atelier = $em->getRepository(Groupe::class)->findByAtelier($atelier);
		
		if(!$groupe && !$eleve_atelier){
			$em->remove($atelier);
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