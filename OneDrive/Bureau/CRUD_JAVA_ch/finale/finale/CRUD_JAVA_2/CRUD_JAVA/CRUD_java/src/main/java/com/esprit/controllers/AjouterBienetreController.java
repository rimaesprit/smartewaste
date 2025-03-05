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

public class AjouterBienetreController {

    @FXML
    private TextField TextFieldNom;

    @FXML
    private TextField TextFieldReview;

    @FXML
    private TextField TextFieldFidality;

    @FXML
    private TextField TextFieldRate; // Affiche la note prédite, non éditable

    private ServiceBienetre serviceBienetre = new ServiceBienetre(); // Instance du service

    /**
     * 🆕 Action : Ajouter un nouvel enregistrement de bien-être avec prédiction de la note
     */
    @FXML
    void ButtonActionAjouter(ActionEvent event) {
        // 🔍 Récupération et validation des champs
        String nom = TextFieldNom.getText().trim();
        String review = TextFieldReview.getText().trim();
        String fidalityStr = TextFieldFidality.getText().trim();

        if (nom.isEmpty() || review.isEmpty() || fidalityStr.isEmpty()) {
            afficherAlerte("⚠️ Champs vides", "Veuillez remplir tous les champs.");
            return;
        }

        // 🎯 Validation de la fidélité (doit être entre 0 et 1)
        double fidality;
        try {
            fidality = Double.parseDouble(fidalityStr);
            if (fidality < 0 || fidality > 1) {
                afficherAlerte("🚫 Erreur de saisie", "La fidélité doit être comprise entre 0 et 1.");
                return;
            }
        } catch (NumberFormatException e) {
            afficherAlerte("🚫 Erreur de saisie", "La fidélité doit être un nombre entre 0 et 1.");
            return;
        }

        // 📊 Prédiction de la note via Flask
        double predictedRate = serviceBienetre.predireNote(review, fidality);
        if (predictedRate == -1) {
            afficherAlerte("❌ Erreur", "Impossible de prédire la note. Vérifiez l'API Flask.");
            return;
        }

        // 🔄 Mise à jour du champ `TextFieldRate`
        TextFieldRate.setText(String.valueOf(predictedRate));

        // 📋 Création de l'objet Bienetre
        bienetre nouveauBienetre = new bienetre(nom, review, predictedRate, fidality);

        // ⚙️ Ajout dans la base de données
        serviceBienetre.ajouter(nouveauBienetre);
        afficherAlerte("✅ Succès", "Bien-être ajouté avec succès !");

        // 🔄 Redirection vers la liste des bien-être
        redirigerVersListeBienetre();
    }

    /**
     * 🚀 Rediriger vers la liste des enregistrements de bien-être
     */
    private void redirigerVersListeBienetre() {
        try {
            Parent root = FXMLLoader.load(getClass().getResource("/ListBienetre.fxml"));
            TextFieldNom.getScene().setRoot(root);
        } catch (IOException e) {
            afficherAlerte("❌ Erreur", "Impossible de charger la liste des bien-être.");
        }
    }

    /**
     * 🔙 Retour au menu principal
     */
    @FXML
    void ButtonActionRetourMenu(ActionEvent event) {
        try {
            Parent root = FXMLLoader.load(getClass().getResource("/Home.fxml"));
            TextFieldNom.getScene().setRoot(root);
        } catch (IOException e) {
            afficherAlerte("❌ Erreur", "Impossible de retourner au menu principal.");
        }
    }

    /**
     * 🛑 Afficher une alerte d'information
     */
    private void afficherAlerte(String titre, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(titre);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
