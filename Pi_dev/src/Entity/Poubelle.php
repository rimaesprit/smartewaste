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
    #[Assert\NotBlank(message: "La localisation est obligatoire")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "La localisation doit contenir au moins {{ limit }} caractères",
        maxMessage: "La localisation ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\-\.,àáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆŠŽ]+$/",
        message: "La localisation contient des caractères non autorisés"
    )]
    private ?string $localisation = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'adresse est obligatoire")]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: "L'adresse doit contenir au moins {{ limit }} caractères",
        maxMessage: "L'adresse ne peut pas dépasser {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s\-\.,àáâäãåąčćęèéêëėįìíîïłńòóôöõøùúûüųūÿýżźñçšžÀÁÂÄÃÅĄĆČĖĘÈÉÊËÌÍÎÏĮŁŃÒÓÔÖÕØÙÚÛÜŲŪŸÝŻŹÑßÇŒÆŠŽ]+$/",
        message: "L'adresse contient des caractères non autorisés"
    )]
    private ?string $adresse = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Le niveau de remplissage est obligatoire")]
    #[Assert\Type(
        type: 'numeric',
        message: "Le niveau de remplissage doit être un nombre"
    )]
    #[Assert\Range(
        min: 0,
        max: 100,
        notInRangeMessage: "Le niveau de remplissage doit être entre {{ min }}% et {{ max }}%"
    )]
    private ?float $niveauRemplissage = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le type de poubelle est obligatoire")]
    #[Assert\Choice(
        choices: ["Plastique", "Verre", "Papier", "Métal", "Organique", "Mixte"],
        message: "Le type doit être l'un des suivants : Plastique, Verre, Papier, Métal, Organique, Mixte"
    )]
    private ?string $type = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ["Fonctionnelle", "En maintenance", "Hors service"],
        message: "Le statut doit être l'un des suivants : Fonctionnelle, En maintenance, Hors service"
    )]
    private ?string $statut = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(
        type: 'numeric',
        message: "La latitude doit être un nombre"
    )]
    #[Assert\Range(
        min: -90,
        max: 90,
        notInRangeMessage: "La latitude doit être comprise entre {{ min }}° et {{ max }}°"
    )]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Type(
        type: 'numeric',
        message: "La longitude doit être un nombre"
    )]
    #[Assert\Range(
        min: -180,
        max: 180,
        notInRangeMessage: "La longitude doit être comprise entre {{ min }}° et {{ max }}°"
    )]
    private ?float $longitude = null;
    
    // Relations possibles ici
    
    public function __construct()
    {
        // Valeurs par défaut
        $this->niveauRemplissage = 0;
        $this->statut = "Fonctionnelle";
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