<?php

namespace App\DataFixtures;

use App\Entity\Poubelle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PoubelleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $locations = [
            'Place de la République',
            'Rue Victor Hugo',
            'Avenue des Champs-Élysées',
            'Boulevard Saint-Michel',
            'Place de la Bastille',
            'Rue de Rivoli',
            'Place de la Concorde',
            'Boulevard Haussmann',
            'Quai des Orfèvres',
            'Rue de la Paix'
        ];
        
        $statuses = ['Fonctionnelle', 'En maintenance', 'Hors service'];
        $types = ['Plastique', 'Verre', 'Papier', 'Métal', 'Organique', 'Mixte'];
        
        foreach ($locations as $index => $location) {
            $poubelle = new Poubelle();
            $poubelle->setLocalisation($location);
            $poubelle->setAdresse($location . ', 75000 Paris, France'); // Adresse fictive
            $poubelle->setNiveauRemplissage(mt_rand(10, 95)); // Niveau de remplissage aléatoire entre 10% et 95%
            $poubelle->setStatut($statuses[array_rand($statuses)]); // Statut aléatoire
            $poubelle->setType($types[array_rand($types)]); // Type aléatoire
            $poubelle->setLatitude(48.8566 + (mt_rand(-100, 100) / 1000)); // Latitude près de Paris
            $poubelle->setLongitude(2.3522 + (mt_rand(-100, 100) / 1000)); // Longitude près de Paris
            
            $manager->persist($poubelle);
            
            // Enregistrer une référence pour pouvoir y accéder dans d'autres fixtures
            $this->addReference('poubelle_' . $index, $poubelle);
        }

        $manager->flush();
    }
}
