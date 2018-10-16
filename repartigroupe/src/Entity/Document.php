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
     * @Assert\File(mimeTypes={ "text/plain" })
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