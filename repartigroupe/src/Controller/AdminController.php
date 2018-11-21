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


class AdminController extends AbstractController
{
	public function index()
	{
		$em = $this->getDoctrine()->getManager();

		$nb_utilisateurs = count($em->getRepository(User::class)->findByRole('ROLE_USER'));
		return $this->render('admin/index.html.twig', array('nb_utilisateurs' => $nb_utilisateurs)); 
	}
}