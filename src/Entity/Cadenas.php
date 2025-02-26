<?php

namespace App\Entity;

use App\Repository\CadenasRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CadenasRepository::class)]
class Cadenas
{
    #[ORM\Id]
    #[ORM\Column(type: "integer")]
    #[ORM\GeneratedValue(strategy: "AUTO")]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idCadenas = null;

    #[ORM\Column(length: 50)]
    private ?string $motSecret = null;

    #[ORM\Column(length: 200)]
    private ?string $image = null;

    #[ORM\ManyToOne(inversedBy: 'cadenas')]
    private ?Plante $idPlante = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdCadenas(): ?int
    {
        return $this->idCadenas;
    }

    public function setIdCadenas(int $idCadenas): static
    {
        $this->idCadenas = $idCadenas;

        return $this;
    }

    public function getMotSecret(): ?string
    {
        return $this->motSecret;
    }

    public function setMotSecret(string $motSecret): static
    {
        $this->motSecret = $motSecret;

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
