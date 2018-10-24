<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupeRepository")
 */
class Groupe
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EleveGroupe", mappedBy="groupe")
     */
    private $eleveGroupes;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $num;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Intervenant", inversedBy="groupes")
     */
    private $intervenant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Salle", inversedBy="groupes")
     */
    private $salle;

    /**
     * @ORM\Column(type="time", nullable=true)
     */
    private $heure;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Atelier", inversedBy="groupes")
     */
    private $atelier;

    /**
     * @ORM\Column(type="integer", length=255, nullable=true)
     */
    private $nbparticipant;

    public function __construct()
    {
        $this->eleve = new ArrayCollection();
        $this->eleveGroupes = new ArrayCollection();
        $this->groupes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

 	/**
     * @return Collection|EleveGroupe[]
     */
    public function getEleveGroupes(): Collection
    {
        return $this->eleveGroupes;
    }

    public function addEleveGroupe(EleveGroupe $eleveGroupe): self
    {
        if (!$this->eleveGroupes->contains($eleveGroupe)) {
            $this->eleveGroupes[] = $eleveGroupe;
            $eleveGroupe->setGroupe($this);
        }

        return $this;
    }

    public function removeEleveGroupe(EleveGroupe $eleveGroupe): self
    {
        if ($this->eleveGroupes->contains($eleveGroupe)) {
            $this->eleveGroupes->removeElement($eleveGroupe);
            // set the owning side to null (unless already changed)
            if ($eleveGroupe->getGroupe() === $this) {
                $eleveGroupe->setGroupe(null);
            }
        }

        return $this;
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

    public function getNum(): ?string
    {
        return $this->num;
    }

    public function setNum(string $num): self
    {
        $this->num = $num;

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

    public function getIntervenant(): ?Intervenant
    {
        return $this->intervenant;
    }

    public function setIntervenant(?Intervenant $intervenant): self
    {
        $this->intervenant = $intervenant;

        return $this;
    }

    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): self
    {
        $this->salle = $salle;

        return $this;
    }

    public function getHeure(): ?\DateTimeInterface
    {
        return $this->heure;
    }

    public function setHeure(?\DateTimeInterface $heure): self
    {
        $this->heure = $heure;

        return $this;
    }

    public function getAtelier(): ?Atelier
    {
        return $this->atelier;
    }

    public function setAtelier(?Atelier $atelier): self
    {
        $this->atelier = $atelier;

        return $this;
    }
}
