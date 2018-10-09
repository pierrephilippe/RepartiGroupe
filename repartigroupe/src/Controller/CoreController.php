<?php
// src/Controller/CoreController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CoreController extends AbstractController
{
    public function index()
    {
        $number = random_int(0, 100);

        return $this->render('index.html.twig', [
            'name' => "toto"
        ]);
    }
}