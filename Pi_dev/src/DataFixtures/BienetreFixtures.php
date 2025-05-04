<?php

namespace App\DataFixtures;

use App\Entity\Bienetre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BienetreFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $noms = [
            'Sophie Martin', 'Thomas Dubois', 'Emma Leroy', 'Lucas Moreau',
            'Chloé Bernard', 'Hugo Petit', 'Léa Durand', 'Nathan Robert',
            'Camille Richard', 'Jules Lambert', 'Manon Girard', 'Maxime Fontaine'
        ];
        
        $reviews = [
            'Très bonne initiative de la ville ! Cette poubelle est toujours propre et bien entretenue.',
            'Je passe devant tous les jours, elle est souvent trop pleine.',
            'Parfait, aucun problème à signaler.',
            'Poubelle régulièrement vidée, c\'est appréciable !',
            'L\'emplacement n\'est pas idéal, il faudrait la déplacer.',
            'Trop petite pour le quartier, elle déborde souvent.',
            'Super propre et bien située. Merci !',
            'Il manque des instructions claires sur le tri.',
            'J\'aimerais qu\'elle soit vidée plus souvent.',
            'Très pratique, bien visible et accessible.',
            'Pas assez de poubelles de ce type dans le quartier.',
            'Parfaitement fonctionnelle et toujours disponible.'
        ];
        
        // Pour chaque poubelle, créer entre 1 et 3 avis
        for ($i = 0; $i < 10; $i++) {
            $referenceName = 'poubelle_' . $i;
            if ($this->hasReference($referenceName)) {
                $nbAvis = mt_rand(1, 3);
                
                for ($j = 0; $j < $nbAvis; $j++) {
                    $avis = new Bienetre();
                    
                    // Récupérer une référence de poubelle
                    $poubelle = $this->getReference($referenceName);
                    
                    // Attribuer un nom aléatoire
                    $avis->setNom($noms[array_rand($noms)]);
                    
                    // Attribuer un avis aléatoire
                    $avis->setReview($reviews[array_rand($reviews)]);
                    
                    // Attribuer une note aléatoire
                    $rate = mt_rand(1, 5);
                    $avis->setRate($rate);
                    
                    // Déterminer le sentiment en fonction de la note
                    $sentiment = 'Indéterminé';
                    if ($rate >= 4) {
                        $sentiment = 'Positif';
                    } elseif ($rate >= 2) {
                        $sentiment = 'Neutre';
                    } else {
                        $sentiment = 'Négatif';
                    }
                    $avis->setSentiment($sentiment);
                    
                    // Associer à une poubelle
                    $avis->setPoubelle($poubelle);
                    
                    $manager->persist($avis);
                }
            }
        }

        $manager->flush();
    }
    
    public function getDependencies(): array
    {
        return [
            PoubelleFixtures::class,
        ];
    }
}
