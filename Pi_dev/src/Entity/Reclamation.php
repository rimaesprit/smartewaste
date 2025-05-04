<?php

namespace App\Entity;

use App\Repository\ReclamationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: ReclamationRepository::class)]
class Reclamation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "ID_rec")]
    private ?int $id = null;

    #[ORM\Column(name: "type_rec", length: 255)]
    #[Assert\NotBlank(message: "Le type de réclamation est obligatoire")]
    private ?string $typeRec = null;

    #[ORM\Column(name: "reclamation", type: Types::TEXT)]
    #[Assert\NotBlank(message: "La description de la réclamation est obligatoire")]
    private ?string $reclamation = null;

    #[ORM\ManyToOne(inversedBy: 'reclamations')]
    private ?User $user = null;
    
    #[ORM\Column(name: "date_rec", type: Types::DATETIME_MUTABLE, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $dateRec = null;
    
    #[ORM\Column(name: "etat_rec", length: 50, options: ["default" => "Pending"])]
    private ?string $etatRec = "Pending";
    
    #[ORM\Column(name: "reponse", type: Types::TEXT, nullable: true)]
    private ?string $reponse = null;
    
    #[ORM\Column(name: "address", type: Types::STRING, length: 255, nullable: true)]
    private ?string $address = null;
    
    #[ORM\Column(name: "latitude", type: Types::FLOAT, nullable: true)]
    private ?float $latitude = null;
    
    #[ORM\Column(name: "longitude", type: Types::FLOAT, nullable: true)]
    private ?float $longitude = null;
    
    #[ORM\Column(name: "photo", type: Types::STRING, length: 255, nullable: true)]
    private ?string $photoName = null;
    
    #[ORM\Column(name: "updated_at", type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;
    
    public function __construct()
    {
        $this->dateRec = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeRec(): ?string
    {
        return $this->typeRec;
    }

    public function setTypeRec(string $typeRec): static
    {
        $this->typeRec = $typeRec;

        return $this;
    }

    public function getReclamation(): ?string
    {
        return $this->reclamation;
    }

    public function setReclamation(string $reclamation): static
    {
        $this->reclamation = $reclamation;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
    
    public function getDateRec(): ?\DateTimeInterface
    {
        return $this->dateRec;
    }

    public function setDateRec(\DateTimeInterface $dateRec): static
    {
        $this->dateRec = $dateRec;

        return $this;
    }
    
    public function getEtatRec(): ?string
    {
        return $this->etatRec;
    }

    public function setEtatRec(string $etatRec): static
    {
        $this->etatRec = $etatRec;

        return $this;
    }
    
    public function getReponse(): ?string
    {
        return $this->reponse;
    }

    public function setReponse(?string $reponse): static
    {
        $this->reponse = $reponse;

        return $this;
    }
    
    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

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
    
    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }

    public function setPhotoName(?string $photoName): static
    {
        $this->photoName = $photoName;

        return $this;
    }
    
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
