<?php

namespace App\Entity;

use App\Repository\MoleculeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoleculeRepository::class)]
class Molecule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idMolecule = null;

    #[ORM\Column(length: 50)]
    private ?string $formule_chimique = null;

    #[ORM\Column(length: 200)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $information = null;

    #[ORM\ManyToOne(inversedBy: 'molecules')]
    private ?Plante $idPlante = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdMolecule(): ?int
    {
        return $this->idMolecule;
    }

    public function setIdMolecule(int $idMolecule): static
    {
        $this->idMolecule = $idMolecule;

        return $this;
    }

    public function getFormuleChimique(): ?string
    {
        return $this->formule_chimique;
    }

    public function setFormuleChimique(string $formule_chimique): static
    {
        $this->formule_chimique = $formule_chimique;

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

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(string $information): static
    {
        $this->information = $information;

        return $this;
    }

    public function getIdPlante(): ?Plante
    {
        return $this->idPlante;
    }

    public function setIdPlante(?Plante $idPlante): static
    {
        $this->idPlante = $idPlante;

        return $this;
    }
}
