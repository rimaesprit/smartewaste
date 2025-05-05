<?php

namespace App\Entity;

use App\Repository\ParkingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ParkingRepository::class)]
#[ORM\Table(name: "parking")]
class Parking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Camion::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le camion ne peut pas être vide")]
    private ?Camion $camion = null;

    #[ORM\ManyToOne(targetEntity: Conteneur::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Le conteneur ne peut pas être vide")]
    private ?Conteneur $conteneur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull(message: "La date d'entrée ne peut pas être vide")]
    private ?\DateTimeInterface $date_entree = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 500, maxMessage: "La description ne peut pas dépasser {{ limit }} caractères")]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getConteneur(): ?Conteneur
    {
        return $this->conteneur;
    }

    public function setConteneur(?Conteneur $conteneur): static
    {
        $this->conteneur = $conteneur;

        return $this;
    }

    public function getDateEntree(): ?\DateTimeInterface
    {
        return $this->date_entree;
    }

    public function setDateEntree(\DateTimeInterface $date_entree): static
    {
        $this->date_entree = $date_entree;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
