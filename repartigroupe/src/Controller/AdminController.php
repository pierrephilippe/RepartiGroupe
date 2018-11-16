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


class AdminController extends AbstractController
{
	public function index()
	{
		return $this->render('admin/index.html.twig'); 
	}
}