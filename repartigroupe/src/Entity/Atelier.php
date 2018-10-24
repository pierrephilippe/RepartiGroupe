<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AtelierRepository")
 */
class Atelier
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
     * @ORM\OneToMany(targetEntity="App\Entity\EleveAtelier", mappedBy="atelier")
     */
    private $eleveAteliers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Groupe", mappedBy="atelier")
     */
    private $groupes;
	
	/**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbparticipant;
	
	/**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $poids;
    
    public function __construct()
    {
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

	public function getNbparticipant(): ?int
    {
        return $this->nbparticipant;
    }

    public function setNbparticipant(int $nbparticipant): self
    {
        $this->nbparticipant = $nbparticipant;

        return $this;
    }

    public function getPoids(): ?int
    {
        return $this->poids;
    }

    public function setPoids(int $poids): self
    {
        $this->poids = $poids;

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
            $eleveAtelier->setAtelier($this);
        }

        return $this;
    }

    public function removeEleveAtelier(EleveAtelier $eleveAtelier): self
    {
        if ($this->eleveAteliers->contains($eleveAtelier)) {
            $this->eleveAteliers->removeElement($eleveAtelier);
            // set the owning side to null (unless already changed)
            if ($eleveAtelier->getAtelier() === $this) {
                $eleveAtelier->setAtelier(null);
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
            $groupe->setAtelier($this);
        }

        return $this;
    }

    public function removeGroupe(Groupe $groupe): self
    {
        if ($this->groupes->contains($groupe)) {
            $this->groupes->removeElement($groupe);
            // set the owning side to null (unless already changed)
            if ($groupe->getAtelier() === $this) {
                $groupe->setAtelier(null);
            }
        }

        return $this;
    }
}
