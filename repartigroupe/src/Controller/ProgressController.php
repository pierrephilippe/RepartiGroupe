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

class ProgressController extends AbstractController
{
    
    public function getProgress(Request $request){
        $percent = 0;
        $compteur = 0;
        $percent = $this->get('session')->get('progress');
        $compteur = $this->get('session')->get('compteur');
        $this->get('session')->save();

        if($percent == 100){
          $session = new Session();
          $session->remove('progress');
        }
    	return new JsonResponse(array('compteur' => $compteur,
                                  	  'percent' => $percent));
    }

}