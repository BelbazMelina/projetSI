<?php

namespace App\Entity;

use App\Repository\PartieRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PartieRepository::class)]
class Partie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idPartie = null;

    #[ORM\Column(length: 50)]
    private ?string $etat = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\ManyToOne(inversedBy: 'parties')]
    private ?Plante $idPlante = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPartie(): ?int
    {
        return $this->idPartie;
    }

    public function setIdPartie(int $idPartie): static
    {
        $this->idPartie = $idPartie;

        return $this;
    }

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

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
