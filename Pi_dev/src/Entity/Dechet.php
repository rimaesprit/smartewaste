<?php

namespace App\Entity;

use App\Repository\DechetRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DechetRepository::class)]
class Dechet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $type_dechet = null;

    #[ORM\Column]
    private ?float $poids = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date_depot = null;

    #[ORM\ManyToOne(inversedBy: 'dechets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Camion $camion = null;

    #[ORM\Column(type: 'boolean')]
    private bool $favori = false;

    #[ORM\Column(type: 'boolean')]
    private bool $traite = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_traitement = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeDechet(): ?string
    {
        return $this->type_dechet;
    }

    public function setTypeDechet(string $type_dechet): static
    {
        $this->type_dechet = $type_dechet;

        return $this;
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(float $poids): static
    {
        $this->poids = $poids;

        return $this;
    }

    public function getDateDepot(): ?\DateTimeInterface
    {
        return $this->date_depot;
    }

    public function setDateDepot(\DateTimeInterface $date_depot): static
    {
        $this->date_depot = $date_depot;

        return $this;
    }

    public function getCamion(): ?Camion
    {
        return $this->camion;
    }

    public function setCamion(?Camion $camion): static
    {
        $this->camion = $camion;

        return $this;
    }

    public function isFavori(): bool
    {
        return $this->favori;
    }

    public function setFavori(bool $favori): static
    {
        $this->favori = $favori;

        return $this;
    }

    public function isTraite(): bool
    {
        return $this->traite;
    }

    public function setTraite(bool $traite): static
    {
        $this->traite = $traite;

        return $this;
    }

    public function getDateTraitement(): ?\DateTimeInterface
    {
        return $this->date_traitement;
    }

    public function setDateTraitement(?\DateTimeInterface $date_traitement): static
    {
        $this->date_traitement = $date_traitement;

        return $this;
    }
}
