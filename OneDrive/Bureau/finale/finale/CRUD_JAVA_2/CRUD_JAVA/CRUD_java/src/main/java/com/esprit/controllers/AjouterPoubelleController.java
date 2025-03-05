package com.esprit.controllers;

import com.esprit.models.Poubelle;
import com.esprit.services.ServicePoubelle;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.TextField;
import javafx.stage.Stage;

import java.io.IOException;

public class AjouterPoubelleController {

    @FXML
    private TextField TextFieldLocalisation;

    @FXML
    private TextField TextFieldNiveauRemplissage;

    @FXML
    private TextField TextFieldStatus;

    @FXML
    void ButtonActionAjouter(ActionEvent event) {
        String localisation = TextFieldLocalisation.getText();
        String niveauStr = TextFieldNiveauRemplissage.getText();
        String status = TextFieldStatus.getText();

        // Vérification des champs
        if (localisation.isEmpty() || niveauStr.isEmpty() || status.isEmpty()) {
            afficherAlerte("⚠️ Erreur", "Veuillez remplir tous les champs !");
            return;
        }

        float niveau;
        try {
            niveau = Float.parseFloat(niveauStr);
        } catch (NumberFormatException e) {
            afficherAlerte("🚫 Erreur", "Le niveau de remplissage doit être un nombre valide !");
            return;
        }

        // Ajout de la poubelle
        Poubelle nouvellePoubelle = new Poubelle(localisation, niveau, status);
        ServicePoubelle service = new ServicePoubelle();
        service.ajouter(nouvellePoubelle);
        afficherAlerte("✅ Succès", "Poubelle ajoutée avec succès !");
    }

    private void afficherAlerte(String titre, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(titre);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
    @FXML
    void ButtonActionRetourMenu(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListPoubelle.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) ((javafx.scene.Node) event.getSource()).getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("🗑️ Gestion des Poubelles");
            stage.show();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
}
