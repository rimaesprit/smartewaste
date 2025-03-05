/*package com.esprit.test;

import com.esprit.models.bienetre;
import com.esprit.models.absence;
import com.esprit.services.ServiceAbsence;
import com.esprit.services.ServiceBienetre;
import com.esprit.services.ServiceConges;
import com.esprit.models.conges;

public class Main {
    public static void main(String[] args) {
        // Création des services
        ServiceAbsence absenceService = new ServiceAbsence();
        ServiceConges congesService = new ServiceConges();
        ServiceBienetre bienetreService = new ServiceBienetre(); // Ajout du service Bienetre

        // Création des objets Absence, Conges, et Bienetre
        absence absence = new absence("non justifiée", "01/02/2025", "05/02/2025", 1, "terminé");
        conges conges = new conges("1er conges", "01/02/2025", "05/02/2025", 1, "terminé");
        bienetre bienetre = new bienetre("Relaxation", "Très bon service", 5); // Exemple d'objet Bienetre

        // Ajout dans la base de données
        absenceService.ajouter(absence);
        congesService.ajouter(conges);
        bienetreService.ajouter(bienetre);  // Ajout du bien-être

        // Modification des objets Absence, Conges, et Bienetre
        absence absenceModifiee = new absence("justifiée", "01/02/2025", "06/02/2025", 1, "en cours");
        absenceService.modifier(absenceModifiee);  // Modification de l'absence de l'employé avec ID 1

        conges congesModifiee = new conges("2eme conges", "01/02/2025", "06/02/2025", 1, "en cours");
        congesService.modifier(congesModifiee);  // Modification du congé de l'employé avec ID 1

        bienetre bienetreModifie = new bienetre("Meditation", "Service amélioré", 4); // Exemple de modification
        bienetreService.modifier(bienetreModifie);  // Modification du bien-être

        // Suppression des objets Absence, Conges, et Bienetre
        absenceService.supprimer(1);  // Suppression de l'absence de l'employé avec ID 1
        congesService.supprimer(1);   // Suppression du congé de l'employé avec ID 1
        bienetreService.supprimer("Relaxation"); // Suppression du bien-être "Relaxation"
    }
}
*/