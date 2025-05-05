<?php

namespace App\Entity;

use App\Repository\SignAbstractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SignAbstractRepository::class)]
#[ORM\Table(name: "signabstract")]
class SignAbstract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "ID_signa")]
    private ?int $id = null;

    #[ORM\Column(name: "type_sign", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "Le type de signalement est obligatoire")]
    private ?string $typeSign = null;

    #[ORM\Column(name: "temps_sign", type: Types::DATETIME_MUTABLE, nullable: true, options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?\DateTimeInterface $tempsSign = null;

    #[ORM\Column(name: "zone", length: 255, nullable: true)]
    #[Assert\NotBlank(message: "La zone est obligatoire")]
    private ?string $zone = null;
    
    #[ORM\Column(name: "adresse", length: 255, nullable: true)]
    private ?string $adresse = null;
    
    #[ORM\Column(name: "description", type: Types::TEXT, nullable: true)]
    #[Assert\NotBlank(message: "La description est obligatoire")]
    private ?string $description = null;
    
    #[ORM\Column(name: "etat_sign", length: 50, options: ["default" => "Pending"])]
    private ?string $etatSign = "Pending";
    
    #[ORM\Column(name: "feedback", type: Types::TEXT, nullable: true)]
    private ?string $feedback = null;

    #[ORM\ManyToOne(inversedBy: 'signalements')]
    private ?User $user = null;

    public function __construct()
    {
        $this->tempsSign = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeSign(): ?string
    {
        return $this->typeSign;
    }

    public function setTypeSign(?string $typeSign): static
    {
        $this->typeSign = $typeSign;

        return $this;
    }

    public function getTempsSign(): ?\DateTimeInterface
    {
        return $this->tempsSign;
    }

    public function setTempsSign(?\DateTimeInterface $tempsSign): static
    {
        $this->tempsSign = $tempsSign;

        return $this;
    }

    public function getZone(): ?string
    {
        return $this->zone;
    }

    public function setZone(?string $zone): static
    {
        $this->zone = $zone;

        return $this;
    }
    
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

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
    
    public function getEtatSign(): ?string
    {
        return $this->etatSign;
    }

    public function setEtatSign(string $etatSign): static
    {
        $this->etatSign = $etatSign;

        return $this;
    }
    
    public function getFeedback(): ?string
    {
        return $this->feedback;
    }

    public function setFeedback(?string $feedback): static
    {
        $this->feedback = $feedback;

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
} 