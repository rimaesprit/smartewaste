package com.esprit.controllers;

import javafx.application.Application;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.Alert;
import javafx.stage.Stage;
import java.io.IOException;

public class Home extends Application {

    public static void main(String[] args) {
        launch(args);
    }

    @Override
    public void start(Stage primaryStage) {
        chargerInterface(primaryStage, "/Home.fxml", "🏡 Menu Principal - Gestion Poubelles & Bien-Êtres");
    }

    /**
     * Charge et affiche une interface donnée.
     * @param stage Fenêtre principale.
     * @param fxmlPath Chemin du fichier FXML à charger.
     * @param titre Titre de la fenêtre.
     */
    private void chargerInterface(Stage stage, String fxmlPath, String titre) {
        try {
            Parent root = FXMLLoader.load(getClass().getResource(fxmlPath));
            Scene scene = new Scene(root);
            stage.setTitle(titre);
            stage.setScene(scene);
            stage.show();
        } catch (IOException e) {
            afficherAlerte("❌ Erreur de chargement", "Impossible de charger l'interface : " + fxmlPath, e.getMessage());
        }
    }

    /**
     * Affiche une alerte en cas d'erreur.
     * @param titre Titre de l'alerte.
     * @param enTete En-tête de l'alerte.
     * @param contenu Message détaillé.
     */
    private void afficherAlerte(String titre, String enTete, String contenu) {
        Alert alert = new Alert(Alert.AlertType.ERROR);
        alert.setTitle(titre);
        alert.setHeaderText(enTete);
        alert.setContentText(contenu);
        alert.showAndWait();
    }
}
