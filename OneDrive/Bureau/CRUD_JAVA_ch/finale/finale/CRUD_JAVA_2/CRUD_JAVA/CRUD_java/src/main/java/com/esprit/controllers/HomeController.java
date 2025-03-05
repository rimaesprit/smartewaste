package com.esprit.controllers;

import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.scene.control.Button;
import javafx.scene.control.Tooltip;
import javafx.stage.Stage;
import java.io.IOException;

public class HomeController {

    @FXML
    private Button btnPoubelles;

    @FXML
    private Button btnBienetres;

    @FXML
    private Button btnQuitter;

    @FXML
    public void initialize() {
        // Ajout de tooltips pour une meilleure expérience utilisateur
        btnPoubelles.setTooltip(new Tooltip("Accédez à la gestion des poubelles"));
        btnBienetres.setTooltip(new Tooltip("Accédez à la gestion des bien-être"));
        btnQuitter.setTooltip(new Tooltip("Quittez l'application"));

        // Gestion des actions
        btnPoubelles.setOnAction(event -> ouvrirInterface("/ListPoubelle.fxml"));
        btnBienetres.setOnAction(event -> ouvrirInterface("/ListBienetre.fxml"));
        btnQuitter.setOnAction(event -> fermerApplication());
    }

    /**
     * Ouvre une interface donnée.
     * @param fxmlPath Chemin du fichier FXML à charger.
     */
    private void ouvrirInterface(String fxmlPath) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();
            Stage stage = (Stage) btnPoubelles.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.show();
        } catch (IOException e) {
            afficherAlerte("❌ Erreur", "Impossible d'ouvrir l'interface", e.getMessage());
        }
    }

    /**
     * Ferme l'application proprement.
     */
    private void fermerApplication() {
        Stage stage = (Stage) btnQuitter.getScene().getWindow();
        stage.close();
    }

    /**
     * Affiche une alerte en cas d'erreur.
     */
    private void afficherAlerte(String titre, String enTete, String contenu) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(titre);
        alert.setHeaderText(enTete);
        alert.setContentText(contenu);
        alert.showAndWait();
    }
}
