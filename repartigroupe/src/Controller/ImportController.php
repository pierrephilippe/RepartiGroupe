<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
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


class ImportController extends AbstractController
{
	private $nb_etape=4;

	public function etape1(Request $request, Reinitialisation $reinitialisation, FileUploader $fileUploader)
	{
		
		$session = new Session();
		$session->set('progress',0);
		$session->set('compteur',0);
		$session->save();
		/*
		 * Upload du fichier CSV | Réinitialisation de la BDD
		 */
		$document = new Document();
        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);
	
        if ($form->isSubmitted() && $form->isValid()) {
        	//on efface les anciens fichiers
			$reinitialisation->effacecsv($this->getParameter('document_directory'));

			//on efface la base
			$reinitialisation->effacebdd();


            // $file stores the uploaded CSV file
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file = $document->getDocument();
			
            //$fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
			$fileName = $fileUploader->upload($file);

            // updates the 'document' property to store the CSV file name
            // instead of its contents
            $document->setDocument($fileName);

            $em = $this->getDoctrine()->getManager();
			$em->persist($document);
			$em->flush();

            return $this->redirect($this->generateUrl('app_etape2'));
        }

        return $this->render('admin/etapes.html.twig',
        	array(
	        	'etape' => 1,
	        	'nb_etape' => $this->nb_etape,
	        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape1'),
	        	'titre_etape' => "Etape 1",
	        	'form' => $form->createView()
        	)
        );  
	}
	public function etape2(Request $request, Import $import)
    {
		/*
		 * Import du fichier uploadé en BDD
		 */

		if ($request->isMethod('POST')){
			//On détermine quel fichier il faut importer
			$nb_fichier = 0;			//pour vérifier qu'il y a un seul CSV		
			$path = "csv/";				//dans le dossier public/csv/

			if($dossier = opendir($path))
			{
				while(false !== ($fichier = readdir($dossier)))
				{	
					$info = new SplFileInfo($fichier, $path, $path);
					if(((strcmp($info->getExtension(),"csv") == 0) || 
						(strcmp($info->getExtension(),"txt") == 0) )
						&& $nb_fichier == 0)
					{
						$nb_fichier++;
						$fichier_a_importer = $path.$fichier;
					}
				}
			}

			//On importe le seul fichier CSV trouvé
			if($nb_fichier == 1){
				//Appel du service 
				$contenucsv = $import->csv($fichier_a_importer);

			}
			return new Response ("Chargement terminé ");
		}
		return $this->render('admin/etapes.html.twig',array(
        	'etape' => 2,
        	'nb_etape' =>$this->nb_etape,
        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape2'),
        	'titre_etape' => "Etape 2 - import en base de donnée",
        	'url_action' => 'app_etape2',
        	'url_suivant' => 'app_etape3'));
    }

	public function etape3(Request $request, Fabriquegroupe $fabriquegroupe)
    {
		/*
		 * Calcul des groupes
		 */
		if ($request->isMethod('POST')){
			//set_time_limit(0);
	        $retour = $fabriquegroupe->calcul();
			return new Response ("Chargement terminé ");
		}
		return $this->render('admin/etapes.html.twig',array(
        	'etape' => 3,
        	'nb_etape' =>$this->nb_etape,
        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape2'),
        	'titre_etape' => "Etape 3 - calcul des groupes",
        	'url_action' => 'app_etape3',
        	'url_suivant' => 'app_etape4'
        ));
    }

    public function etape4(Request $request, Export $export)
    {

		/*
		 * Mise à disposition d'un fichier CSV contenant les nouvelles données calculées
		 */
	    $retour = $export->tableau();

		
		return $this->render('admin/etapes.html.twig',array(
        	'etape' => 4,
        	'nb_etape' =>$this->nb_etape,
        	//'titre_etape' => $this->get('translator')->trans('intranet.import.etape2'),
        	'titre_etape' => "Etape 4 - Génération du fichier de retour",
        	'retour' => $retour));
    }

    public function retourner_csv_secretariat(Export $export)
    {
        /*
		 * Calcul du CSV de retour
		 */
        return $export->csv_secretariat();
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}