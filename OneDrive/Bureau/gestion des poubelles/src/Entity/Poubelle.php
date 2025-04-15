<?php

namespace App\Entity;

use App\Repository\PoubelleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PoubelleRepository::class)]
class Poubelle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La localisation ne peut pas être vide")]
    private ?string $localisation = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse ne peut pas être vide")]
    private ?string $adresse = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le niveau de remplissage ne peut pas être vide")]
    #[Assert\Range(
        min: 0,
        max: 100,
        notInRangeMessage: "Le niveau de remplissage doit être entre {{ min }}% et {{ max }}%"
    )]
    private ?float $niveauRemplissage = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type de poubelle ne peut pas être vide")]
    #[Assert\Choice(
        choices: ["Plastique", "Verre", "Papier", "Métal", "Organique", "Mixte"],
        message: "Le type doit être l'un des suivants : Plastique, Verre, Papier, Métal, Organique, Mixte"
    )]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut ne peut pas être vide")]
    #[Assert\Choice(
        choices: ["Fonctionnelle", "En maintenance", "Hors service"],
        message: "Le statut doit être l'un des suivants : Fonctionnelle, En maintenance, Hors service"
    )]
    private ?string $statut = null;

    #[ORM\Column(nullable: true)]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $longitude = null;
    
    // Relations possibles ici
    
    public function __construct()
    {
        
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocalisation(): ?string
    {
        return $this->localisation;
    }

    public function setLocalisation(string $localisation): static
    {
        $this->localisation = $localisation;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getNiveauRemplissage(): ?float
    {
        return $this->niveauRemplissage;
    }

    public function setNiveauRemplissage(float $niveauRemplissage): static
    {
        $this->niveauRemplissage = $niveauRemplissage;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function __toString(): string
    {
        return $this->localisation;
    }
} 