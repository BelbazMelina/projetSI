<?php

namespace App\Entity;

use App\Repository\PlanteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanteRepository::class)]
class Plante
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idPlante = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 200)]
    private ?string $image = null;

    /**
     * @var Collection<int, Molecule>
     */
    #[ORM\OneToMany(targetEntity: Molecule::class, mappedBy: 'idPlante')]
    private Collection $molecules;

    /**
     * @var Collection<int, Cadenas>
     */
    #[ORM\OneToMany(targetEntity: Cadenas::class, mappedBy: 'idPlante')]
    private Collection $cadenas;

    /**
     * @var Collection<int, Partie>
     */
    #[ORM\OneToMany(targetEntity: Partie::class, mappedBy: 'idPlante')]
    private Collection $parties;

    public function __construct()
    {
        $this->molecules = new ArrayCollection();
        $this->cadenas = new ArrayCollection();
        $this->parties = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPlante(): ?int
    {
        return $this->idPlante;
    }

    public function setIdPlante(int $idPlante): static
    {
        $this->idPlante = $idPlante;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Molecule>
     */
    public function getMolecules(): Collection
    {
        return $this->molecules;
    }

    public function addMolecule(Molecule $molecule): static
    {
        if (!$this->molecules->contains($molecule)) {
            $this->molecules->add($molecule);
            $molecule->setIdPlante($this);
        }

        return $this;
    }

    public function removeMolecule(Molecule $molecule): static
    {
        if ($this->molecules->removeElement($molecule)) {
            // set the owning side to null (unless already changed)
            if ($molecule->getIdPlante() === $this) {
                $molecule->setIdPlante(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cadenas>
     */
    public function getCadenas(): Collection
    {
        return $this->cadenas;
    }

    public function addCadena(Cadenas $cadena): static
    {
        if (!$this->cadenas->contains($cadena)) {
            $this->cadenas->add($cadena);
            $cadena->setIdPlante($this);
        }

        return $this;
    }

    public function removeCadena(Cadenas $cadena): static
    {
        if ($this->cadenas->removeElement($cadena)) {
            // set the owning side to null (unless already changed)
            if ($cadena->getIdPlante() === $this) {
                $cadena->setIdPlante(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Partie>
     */
    public function getParties(): Collection
    {
        return $this->parties;
    }

    public function addParty(Partie $party): static
    {
        if (!$this->parties->contains($party)) {
            $this->parties->add($party);
            $party->setIdPlante($this);
        }

        return $this;
    }

    public function removeParty(Partie $party): static
    {
        if ($this->parties->removeElement($party)) {
            // set the owning side to null (unless already changed)
            if ($party->getIdPlante() === $this) {
                $party->setIdPlante(null);
            }
        }

        return $this;
    }
}
