<?php

namespace App\Entity;

use App\Repository\CamionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CamionRepository::class)]
#[UniqueEntity(fields: ['matricule'], message: 'Cette matricule existe déjà')]
class Camion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'La matricule ne peut pas être vide')]
    #[Assert\Regex(
        pattern: '/^[A-Z]{2,3}-[0-9]{3,4}$/',
        message: 'La matricule doit être au format XX-000 ou XXX-0000 (lettres majuscules)'
    )]
    private ?string $matricule = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le modèle ne peut pas être vide')]
    private ?string $modele = null;

    #[ORM\Column]
    #[Assert\NotNull(message: 'La capacité ne peut pas être vide')]
    #[Assert\Range(
        min: 1,
        max: 50,
        notInRangeMessage: 'La capacité doit être comprise entre {{ min }} et {{ max }} tonnes'
    )]
    private ?float $capacite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "L'état ne peut pas être vide")]
    #[Assert\Choice(
        choices: ['en_service', 'en_maintenance', 'hors_service'],
        message: "L'état doit être l'un des suivants : En service, En maintenance, Hors service"
    )]
    private ?string $etat = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank(message: 'Le type de moteur ne peut pas être vide')]
    #[Assert\Choice(
        choices: ['diesel', 'electrique', 'hybride', 'gaz', 'biodiesel'],
        message: 'Le type de moteur doit être l\'un des suivants : Diesel, Électrique, Hybride, Gaz, Biodiesel'
    )]
    private ?string $type_moteur = 'diesel';
    
    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: 'Les émissions CO2 ne peuvent pas être vides')]
    #[Assert\Range(
        min: 0,
        max: 1000,
        notInRangeMessage: 'Les émissions CO2 doivent être comprises entre {{ min }} et {{ max }} g/km'
    )]
    private ?float $emission_co2 = 180.0;
    
    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: 'La consommation ne peut pas être vide')]
    #[Assert\Range(
        min: 0,
        max: 50,
        notInRangeMessage: 'La consommation doit être comprise entre {{ min }} et {{ max }} L/100km'
    )]
    private ?float $consommation = 25.0;
    
    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: "L'année de fabrication ne peut pas être vide")]
    #[Assert\Range(
        min: 2000,
        max: 2024,
        notInRangeMessage: "L'année de fabrication doit être comprise entre {{ min }} et {{ max }}"
    )]
    private ?int $annee_fabrication = null;
    
    #[ORM\Column(nullable: true)]
    #[Assert\NotNull(message: 'Le kilométrage ne peut pas être vide')]
    #[Assert\PositiveOrZero(message: 'Le kilométrage doit être un nombre positif')]
    private ?float $kilometrage = 0.0;

    #[ORM\OneToMany(mappedBy: 'camion', targetEntity: Dechet::class)]
    private Collection $dechets;

<<<<<<< HEAD
=======
    #[ORM\Column(type: 'boolean')]
    private bool $en_tournee = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $debut_tournee = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $fin_tournee = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $destination = null;

>>>>>>> master
    public function __construct()
    {
        $this->dechets = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): static
    {
        $this->matricule = $matricule;

        return $this;
    }

    public function getModele(): ?string
    {
        return $this->modele;
    }

    public function setModele(string $modele): static
    {
        $this->modele = $modele;

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

    public function getEtat(): ?string
    {
        return $this->etat;
    }

    public function setEtat(string $etat): static
    {
        $this->etat = $etat;

        return $this;
    }
    
    public function getTypeMoteur(): ?string
    {
        return $this->type_moteur;
    }

    public function setTypeMoteur(?string $type_moteur): static
    {
        $this->type_moteur = $type_moteur;

        return $this;
    }
    
    public function getTypeCarburant(): ?string
    {
        return $this->type_moteur;
    }
    
    public function getEmissionCo2(): ?float
    {
        return $this->emission_co2;
    }

    public function setEmissionCo2(?float $emission_co2): static
    {
        $this->emission_co2 = $emission_co2;

        return $this;
    }
    
    public function getConsommation(): ?float
    {
        return $this->consommation;
    }

    public function setConsommation(?float $consommation): static
    {
        $this->consommation = $consommation;

        return $this;
    }
    
    public function getAnneeFabrication(): ?int
    {
        return $this->annee_fabrication;
    }

    public function setAnneeFabrication(?int $annee_fabrication): static
    {
        $this->annee_fabrication = $annee_fabrication;

        return $this;
    }
    
    public function getKilometrage(): ?float
    {
        return $this->kilometrage;
    }

    public function setKilometrage(?float $kilometrage): static
    {
        $this->kilometrage = $kilometrage;

        return $this;
    }

    /**
     * @return Collection<int, Dechet>
     */
    public function getDechets(): Collection
    {
        return $this->dechets;
    }

    public function addDechet(Dechet $dechet): static
    {
        if (!$this->dechets->contains($dechet)) {
            $this->dechets->add($dechet);
            $dechet->setCamion($this);
        }

        return $this;
    }

    public function removeDechet(Dechet $dechet): static
    {
        if ($this->dechets->removeElement($dechet)) {
            // set the owning side to null (unless already changed)
            if ($dechet->getCamion() === $this) {
                $dechet->setCamion(null);
            }
        }

        return $this;
    }
    
    /**
     * Calcule le score environnemental du camion sur une échelle de 0 à 100
     * Plus le score est élevé, plus le camion est écologique
     */
    public function getScoreEnvironnemental(): int
    {
        // Paramètres de calcul
        $scoreMax = 100;
        $scoreTypeMoteur = 0;
        $scoreEmission = 0;
        $scoreConsommation = 0;
        $scoreAge = 0;
        
        // Score par type de moteur (0-25)
        switch ($this->type_moteur) {
            case 'electrique':
                $scoreTypeMoteur = 25;
                break;
            case 'hybride':
                $scoreTypeMoteur = 18;
                break;
            case 'gaz':
                $scoreTypeMoteur = 12;
                break;
            case 'biodiesel':
                $scoreTypeMoteur = 10;
                break;
            case 'diesel':
            default:
                $scoreTypeMoteur = 5;
                break;
        }
        
        // Score par émission CO2 (0-25)
        // Moins d'émissions = meilleur score
        if ($this->emission_co2 <= 50) {
            $scoreEmission = 25;
        } elseif ($this->emission_co2 <= 100) {
            $scoreEmission = 20;
        } elseif ($this->emission_co2 <= 150) {
            $scoreEmission = 15;
        } elseif ($this->emission_co2 <= 200) {
            $scoreEmission = 10;
        } elseif ($this->emission_co2 <= 250) {
            $scoreEmission = 5;
        } else {
            $scoreEmission = 0;
        }
        
        // Score par consommation (0-25)
        // Moins de consommation = meilleur score
        if ($this->consommation <= 10) {
            $scoreConsommation = 25;
        } elseif ($this->consommation <= 15) {
            $scoreConsommation = 20;
        } elseif ($this->consommation <= 20) {
            $scoreConsommation = 15;
        } elseif ($this->consommation <= 25) {
            $scoreConsommation = 10;
        } elseif ($this->consommation <= 30) {
            $scoreConsommation = 5;
        } else {
            $scoreConsommation = 0;
        }
        
        // Score par âge (0-25)
        $anneeActuelle = (int) date('Y');
        $age = $this->annee_fabrication ? $anneeActuelle - $this->annee_fabrication : 10;
        
        if ($age <= 2) {
            $scoreAge = 25;
        } elseif ($age <= 4) {
            $scoreAge = 20;
        } elseif ($age <= 6) {
            $scoreAge = 15;
        } elseif ($age <= 8) {
            $scoreAge = 10;
        } elseif ($age <= 10) {
            $scoreAge = 5;
        } else {
            $scoreAge = 0;
        }
        
        // Score total
        $scoreTotal = $scoreTypeMoteur + $scoreEmission + $scoreConsommation + $scoreAge;
        
        return min($scoreTotal, $scoreMax);
    }
    
    /**
     * Renvoie le niveau écologique du camion
     */
    public function getNiveauEcologique(): string
    {
        $score = $this->getScoreEnvironnemental();
        
        if ($score >= 80) {
            return 'Excellent';
        } elseif ($score >= 60) {
            return 'Bon';
        } elseif ($score >= 40) {
            return 'Moyen';
        } elseif ($score >= 20) {
            return 'Médiocre';
        } else {
            return 'Mauvais';
        }
    }
<<<<<<< HEAD
}
=======

    public function isEnTournee(): bool
    {
        return $this->en_tournee;
    }

    public function setEnTournee(bool $en_tournee): static
    {
        $this->en_tournee = $en_tournee;

        return $this;
    }

    public function getDebutTournee(): ?\DateTimeInterface
    {
        return $this->debut_tournee;
    }

    public function setDebutTournee(?\DateTimeInterface $debut_tournee): static
    {
        $this->debut_tournee = $debut_tournee;

        return $this;
    }

    public function getFinTournee(): ?\DateTimeInterface
    {
        return $this->fin_tournee;
    }

    public function setFinTournee(?\DateTimeInterface $fin_tournee): static
    {
        $this->fin_tournee = $fin_tournee;

        return $this;
    }

    public function getDestination(): ?string
    {
        return $this->destination;
    }

    public function setDestination(?string $destination): static
    {
        $this->destination = $destination;

        return $this;
    }

    /**
     * Démarre une tournée pour ce camion
     */
    public function demarrerTournee(?string $destination = null): static
    {
        if ($this->etat !== 'en_service') {
            throw new \LogicException('Le camion doit être en service pour démarrer une tournée');
        }

        if ($this->en_tournee) {
            throw new \LogicException('Le camion est déjà en tournée');
        }

        $this->en_tournee = true;
        $this->debut_tournee = new \DateTime();
        $this->fin_tournee = null;
        $this->destination = $destination;

        return $this;
    }

    /**
     * Termine la tournée de ce camion
     */
    public function terminerTournee(): static
    {
        if (!$this->en_tournee) {
            throw new \LogicException('Le camion n\'est pas en tournée');
        }

        $this->en_tournee = false;
        $this->fin_tournee = new \DateTime();

        return $this;
    }

    /**
     * Vérifie si le camion est actuellement disponible pour une tournée
     */
    public function isDisponible(): bool
    {
        return $this->etat === 'en_service' && !$this->en_tournee;
    }

    /**
     * Retourne la durée de la tournée actuelle ou de la dernière tournée
     */
    public function getDureeTournee(): ?string
    {
        if ($this->debut_tournee === null) {
            return null;
        }

        $debut = $this->debut_tournee;
        $fin = $this->en_tournee ? new \DateTime() : $this->fin_tournee;

        if ($fin === null) {
            return null;
        }

        $interval = $debut->diff($fin);
        
        if ($interval->days > 0) {
            return $interval->format('%a jours %h heures %i minutes');
        }
        
        return $interval->format('%h heures %i minutes');
    }
}
 
>>>>>>> master
