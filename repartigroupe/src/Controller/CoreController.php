<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpKernel\Exception\Exception;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Finder\SplFileInfo;
use App\Googleform\Import;
use App\Calculateur\Fabriquegroupe;
use App\Calculateur\Export;
use League\Csv\Reader;
use League\Csv\Statement;
use App\Entity\EleveAtelier;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Services\Reinitialisation;
use App\Services\FileUploader;


class CoreController extends AbstractController
{
	private $nb_etape=4;


	public function index()
	{
        //selon le type de visiteur, on redirige :
        if( $this->isGranted('ROLE_SUPER_ADMIN') )
        {
			return $this->redirectToRoute('app_superadmin_config');
		}
		elseif( $this->isGranted('ROLE_ADMIN') )
        {
			return $this->redirectToRoute('app_admin_accueil'); 
		}
        elseif( $this->isGranted('ROLE_USER') )
        {
			return $this->render('user/index.html.twig');  
		}
		else 
		{
			throw new Exception('Accès interdit si non identifié');
		}
	}


}