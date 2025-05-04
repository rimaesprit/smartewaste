<?php

namespace App\Entity;

use App\Repository\BienetreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BienetreRepository::class)]
class Bienetre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $nom = null;

    #[ORM\Column(type: "text")]
    #[Assert\NotBlank(message: "L'avis ne peut pas être vide")]
    #[Assert\Length(
        min: 10,
        max: 1000,
        minMessage: "L'avis doit contenir au moins {{ limit }} caractères",
        maxMessage: "L'avis ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $review = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La note ne peut pas être vide")]
    #[Assert\Range(
        min: 1,
        max: 5,
        notInRangeMessage: "La note doit être entre {{ min }} et {{ max }}"
    )]
    private ?int $rate = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Le sentiment ne peut pas être vide")]
    #[Assert\Choice(
        choices: ["Positif", "Négatif", "Neutre", "Indéterminé"],
        message: "Le sentiment doit être l'un des suivants : Positif, Négatif, Neutre, Indéterminé"
    )]
    private ?string $sentiment = "Indéterminé";

    // On pourrait ajouter une relation avec l'entité Poubelle ici
    #[ORM\ManyToOne(targetEntity: Poubelle::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner une poubelle")]
    private ?Poubelle $poubelle = null;

    public function __construct()
    {
        $this->sentiment = "Indéterminé";
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(string $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getRate(): ?int
    {
        return $this->rate;
    }

    public function setRate(int $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getSentiment(): ?string
    {
        return $this->sentiment;
    }

    public function setSentiment(?string $sentiment): static
    {
        $this->sentiment = $sentiment;

        return $this;
    }

    public function getPoubelle(): ?Poubelle
    {
        return $this->poubelle;
    }

    public function setPoubelle(?Poubelle $poubelle): static
    {
        $this->poubelle = $poubelle;

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
} 