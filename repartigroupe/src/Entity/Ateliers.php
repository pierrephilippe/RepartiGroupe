<?php
namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;

class Ateliers
{
    protected $listeateliers;

    public function __construct()
    {
        $this->listeateliers = new ArrayCollection();
    }

    public function getListeateliers()
    {
        return $this->listeateliers;
    }
}