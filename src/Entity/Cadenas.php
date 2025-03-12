<?php

namespace App\Entity;

use App\Repository\CadenasRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CadenasRepository::class)]
#[ORM\Table(name: 'cadenas')]
class Cadenas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id= null;

    #[ORM\Column(length: 50)]
    private ?string $motSecret = null;

    #[ORM\Column(length: 200, nullable: true)]
    private ?string $image = null;

    #[ORM\ManyToOne(targetEntity: Plante::class, inversedBy: 'cadenas')]
    #[ORM\JoinColumn(referencedColumnName: 'id', nullable: false)]
    private ?Plante $plante = null;

    public function getIdCadenas(): ?int
    {
        return $this->idCadenas;
    }

    public function getMotSecret(): ?string
    {
        return $this->motSecret;
    }

    public function setMotSecret(string $motSecret): self
    {
        $this->motSecret = $motSecret;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getPlante(): ?Plante
    {
        return $this->plante;
    }
    public function getId(): ?Integer
    {
        return $this->id;
    }
    public function setId(?int $id): ?self
    {
        $this->id=$id;
        return $this;
    }

    public function setPlante(?Plante $plante): self
    {
        $this->plante = $plante;
        return $this;
    }
}