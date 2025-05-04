<?php

namespace App\Entity;

use App\Repository\ConteneurRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConteneurRepository::class)]
class Conteneur
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de déchet ne peut pas être vide")]
    #[Assert\Length(min: 2, max: 50, minMessage: "Le type de déchet doit comporter au moins {{ limit }} caractères", maxMessage: "Le type de déchet ne peut pas dépasser {{ limit }} caractères")]
    private ?string $type_dechet = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "La capacité ne peut pas être vide")]
    #[Assert\Positive(message: "La capacité doit être un nombre positif")]
    #[Assert\LessThan(value: 10000, message: "La capacité ne peut pas dépasser 10 000 kg")]
    private ?float $capacite = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le poids actuel ne peut pas être vide")]
    #[Assert\PositiveOrZero(message: "Le poids actuel doit être un nombre positif ou zéro")]
    #[Assert\Expression(
        "this.getPoidsActuel() <= this.getCapacite()",
        message: "Le poids actuel ne peut pas dépasser la capacité"
    )]
    private ?float $poids_actuel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'emplacement ne peut pas être vide")]
    #[Assert\Length(min: 3, max: 100, minMessage: "L'emplacement doit comporter au moins {{ limit }} caractères", maxMessage: "L'emplacement ne peut pas dépasser {{ limit }} caractères")]
    private ?string $emplacement = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La zone ne peut pas être vide")]
    private ?string $zone = null;

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

    public function getCapacite(): ?float
    {
        return $this->capacite;
    }

    public function setCapacite(float $capacite): static
    {
        $this->capacite = $capacite;

        return $this;
    }

    public function getPoidsActuel(): ?float
    {
        return $this->poids_actuel;
    }

    public function setPoidsActuel(float $poids_actuel): static
    {
        $this->poids_actuel = $poids_actuel;

        return $this;
    }

    public function getEmplacement(): ?string
    {
        return $this->emplacement;
    }

    public function setEmplacement(string $emplacement): static
    {
        $this->emplacement = $emplacement;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(string $zone): static
    {
        $this->zone = $zone;

        return $this;
    }
}
