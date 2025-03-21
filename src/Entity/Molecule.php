<?php

namespace App\Entity;

use App\Repository\MoleculeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MoleculeRepository::class)]
#[ORM\Table(name: 'molecule')]
class Molecule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column( type: 'integer')]
    private ?int $id= null;

    #[ORM\Column(length: 50)]
    private ?string $formuleChimique = null;

    #[ORM\Column(length: 200)]
    private ?string $image = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $information = null;

    #[ORM\ManyToOne(targetEntity: Plante::class, inversedBy: 'molecules')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    private ?Plante $plante = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function setId(?int $id): ?self
    {
        $this->id=$id;
        return $this;
    }

    public function getFormuleChimique(): ?string
    {
        return $this->formuleChimique;
    }

    public function setFormuleChimique(string $formuleChimique): self
    {
        $this->formuleChimique = $formuleChimique;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(string $information): self
    {
        $this->information = $information;
        return $this;
    }

    public function getPlante(): ?Plante
    {
        return $this->plante;
    }

    public function setPlante(?Plante $plante): self
    {
        $this->plante = $plante;
        return $this;
    }
    public function getTotalAtoms(): int
    {
        $formula = $this->formuleChimique;
        $total = 0;

        // Exemple de calcul simple (à adapter selon vos besoins)
        preg_match_all('/\d+/', $formula, $matches);
        foreach ($matches[0] as $number) {
            $total += (int)$number;
        }

        return $total;
    }
}