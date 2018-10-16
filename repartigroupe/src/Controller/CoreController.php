<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Finder\SplFileInfo;

use App\Googleform\Import;
use App\Calculateur\Fabriquegroupe;
use App\Calculateur\Export;

use League\Csv\Reader;
use League\Csv\Statement;

use App\Entity\EleveAtelier;
use App\Entity\Document;
use App\Form\DocumentType;

class CoreController extends AbstractController
{
  	public function index(Request $request)
    {
        $document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded PDF file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $document->getDocument();

            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $file->move(
                    $this->getParameter('document_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            // updates the 'brochure' property to store the PDF file name
            // instead of its contents
            $document->setDocument($fileName);

            // ... persist the $product variable or any other work

            return $this->redirect($this->generateUrl('app_product_list'));
        }

        return $this->render('index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function lirecsv(Import $import)
    {

		
		//On détermine quel fichier il faut importer

		$nb_fichier = 0;			//pour vérifier qu'il y a un seul CSV		
		$path = "csv/";				//dans le dossier public/csv/
		
		if($dossier = opendir($path))
		{
			while(false !== ($fichier = readdir($dossier)))
			{	
				$info = new SplFileInfo($fichier, $path, $path);
				if((strcmp($info->getExtension(),"csv") == 0) && $nb_fichier == 0)
				{
					$nb_fichier++;
					$fichier_a_importer = $path.$fichier;
				}
			}
		}

		

		//On importer le seul fichier CSV trouvé
		if($nb_fichier == 1){
			//Appel du service 
			$contenucsv = $import->csv($fichier_a_importer);

		}
        return $this->render('csv/lire.html.twig',
        	array(  'contenucsv' => $contenucsv)
        );   
    }

    public function calculgroupe(Fabriquegroupe $fabriquegroupe)
    {
        set_time_limit(0);

        /*
         * On récupère tous les champs EleveAtelier dans un tableau
         */
        $retour = $fabriquegroupe->calcul();
        return $this->render('calculgroupe.html.twig',
    		array('retour' => $retour)
    	);
    }

    public function afficherresultats(Export $export)
    {
        /*
         * On récupère tous les champs EleveAtelier dans un tableau
         */
        $retour = $export->tableau();
        return $this->render('resultats.html.twig',
    		array('retour' => $retour)
    	);
    }

    public function retournercsv(Export $export)
    {
        /*
         * On récupère tous les champs EleveAtelier dans un tableau
         */
        return $export->csv();
    }
}