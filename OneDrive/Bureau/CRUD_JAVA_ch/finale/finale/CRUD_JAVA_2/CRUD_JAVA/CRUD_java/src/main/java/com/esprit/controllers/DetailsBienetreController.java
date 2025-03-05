package com.esprit.controllers;

import com.esprit.models.bienetre;
import com.esprit.services.ServiceBienetre;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.control.Alert;
import javafx.scene.control.TextField;

import java.io.IOException;

public class DetailsBienetreController {

    @FXML
    private TextField ResultatNom;

    @FXML
    private TextField ResultatReview;

    @FXML
    private TextField ResultatFidality;



    private String nom;
    private double rate; // ✅ Stocke l'ancienne valeur pour éviter qu'elle ne change

    /**
     * ✅ Définir les champs de détails avec les valeurs du bien-être sélectionné.
     */
    public void setDetailsBienetre(String nom, String review, double fidality, double rate) {
        this.nom = nom;

        ResultatNom.setText(nom);
        ResultatReview.setText(review);
        ResultatFidality.setText(String.valueOf(fidality));

    }

    /**
     * ✏️ Modifier un enregistrement de bien-être.
     */
    @FXML
    void ButtonActionModifier(ActionEvent event) {
        String nom = ResultatNom.getText().trim();
        String review = ResultatReview.getText().trim();
        String fidalityStr = ResultatFidality.getText().trim();

        if (nom.isEmpty() || review.isEmpty() || fidalityStr.isEmpty()) {
            afficherAlerte("⚠️ Erreur", "Tous les champs doivent être remplis.");
            return;
        }

        // 🎯 Validation du `fidality`
        double fidality;
        try {
            fidality = Double.parseDouble(fidalityStr);
            if (fidality < 0 || fidality > 1) {
                afficherAlerte("⚠️ Erreur", "La fidélité doit être comprise entre 0 et 1.");
                return;
            }
        } catch (NumberFormatException e) {
            afficherAlerte("🚫 Erreur", "La fidélité doit être un nombre valide.");
            return;
        }

        // ✅ NE PAS MODIFIER `rate` → Utiliser la valeur stockée
        bienetre updatedBienetre = new bienetre(nom, review, fidality, this.rate);
        ServiceBienetre serviceBienetre = new ServiceBienetre();
        serviceBienetre.modifier(updatedBienetre);

        afficherAlerte("✅ Succès", "Les détails du bien-être ont été mis à jour.");
        rediriger("/ListBienetre.fxml");
    }

    /**
     * 🗑️ Supprimer un enregistrement de bien-être.
     */
    @FXML
    void ButtonActionSupprimer(ActionEvent event) {
        ServiceBienetre serviceBienetre = new ServiceBienetre();

        // ✅ Vérifie si l'objet existe avant de le supprimer
        boolean deleted = serviceBienetre.supprimer(nom);
        if (deleted) {
            afficherAlerte("🗑️ Suppression", "Le bien-être a été supprimé avec succès.");
        } else {
            afficherAlerte("⚠️ Erreur", "Impossible de supprimer : l'élément n'existe pas.");
        }

        // 🔙 Redirection vers la liste des bien-êtres
        rediriger("/ListBienetre.fxml");
    }

    /**
     * 🔙 Retourner au menu principal.
     */
    @FXML
    void ButtonActionRetourMenu(ActionEvent event) {
        rediriger("/Home.fxml");
    }

    /**
     * 🌐 Rediriger vers une autre vue.
     */
    private void rediriger(String cheminFXML) {
        try {
            Parent root = FXMLLoader.load(getClass().getResource(cheminFXML));
            ResultatNom.getScene().setRoot(root);
        } catch (IOException e) {
            afficherAlerte("❌ Erreur", "Impossible de charger la vue : " + e.getMessage());
        }
    }

    /**
     * ⚠️ Afficher une alerte.
     */
    private void afficherAlerte(String titre, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(titre);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
