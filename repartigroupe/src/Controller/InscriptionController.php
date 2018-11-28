<?php 
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Services\Reinitialisation;

class InscriptionController extends AbstractController
{
    
    public function index(Request $request){
        return $this->render('admin/inscriptions/index.html.twig');
    }

}