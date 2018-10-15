<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EleveRepository")
 */
class Eleve
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $prenom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Classe", inversedBy="eleves")
     */
    private $classe;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EleveAtelier", mappedBy="eleve")
     */
    private $eleveAteliers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Groupe", mappedBy="eleve")
     */
    private $groupes;

    public function __construct()
    {
        $this->atelier = new ArrayCollection();
        $this->eleveAteliers = new ArrayCollection();
        $this->groupes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getClasse(): ?Classe
    {
        return $this->classe;
    }

    public function setClasse(?Classe $classe): self
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * @return Collection|EleveAtelier[]
     */
    public function getAtelier(): Collection
    {
        return $this->atelier;
    }

    public function addAtelier(EleveAtelier $atelier): self
    {
        if (!$this->atelier->contains($atelier)) {
            $this->atelier[] = $atelier;
            $atelier->setEleve($this);
        }

        return $this;
    }

    public function removeAtelier(EleveAtelier $atelier): self
    {
        if ($this->atelier->contains($atelier)) {
            $this->atelier->removeElement($atelier);
            // set the owning side to null (unless already changed)
            if ($atelier->getEleve() === $this) {
                $atelier->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EleveAtelier[]
     */
    public function getEleveAteliers(): Collection
    {
        return $this->eleveAteliers;
    }

    public function addEleveAtelier(EleveAtelier $eleveAtelier): self
    {
        if (!$this->eleveAteliers->contains($eleveAtelier)) {
            $this->eleveAteliers[] = $eleveAtelier;
            $eleveAtelier->setEleve($this);
        }

        return $this;
    }

    public function removeEleveAtelier(EleveAtelier $eleveAtelier): self
    {
        if ($this->eleveAteliers->contains($eleveAtelier)) {
            $this->eleveAteliers->removeElement($eleveAtelier);
            // set the owning side to null (unless already changed)
            if ($eleveAtelier->getEleve() === $this) {
                $eleveAtelier->setEleve(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Groupe[]
     */
    public function getGroupes(): Collection
    {
        return $this->groupes;
    }

    public function addGroupe(Groupe $groupe): self
    {
        if (!$this->groupes->contains($groupe)) {
            $this->groupes[] = $groupe;
            $groupe->addEleve($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        if ($this->groupes->contains($groupe)) {
            $this->groupes->removeElement($groupe);
            $groupe->removeEleve($this);
        }

        return $this;
    }
}
