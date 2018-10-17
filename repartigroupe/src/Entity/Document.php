<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

class Document
{
    // ...

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Sélectionnez le fichier csv")
     * @Assert\File(
     *     maxSize = "2048k",
     *     mimeTypes={ "text/plain", "text/csv" }),
     *     mimeTypesMessage = "Veuillez charger un fichier CSV valide"
     */
    private $document;

    public function getDocument()
    {
        return $this->document;
    }

    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }
}