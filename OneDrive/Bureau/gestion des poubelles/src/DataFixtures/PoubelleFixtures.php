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
        
        $statuses = ['Disponible', 'En maintenance', 'Hors service'];
        
        foreach ($locations as $index => $location) {
            $poubelle = new Poubelle();
            $poubelle->setLocalisation($location);
            $poubelle->setNiveauRemplissage(mt_rand(10, 95)); // Niveau de remplissage aléatoire entre 10% et 95%
            $poubelle->setStatus($statuses[array_rand($statuses)]); // Statut aléatoire
            
            $manager->persist($poubelle);
            
            // Enregistrer une référence pour pouvoir y accéder dans d'autres fixtures
            $this->addReference('poubelle_' . $index, $poubelle);
        }

        $manager->flush();
    }
}
