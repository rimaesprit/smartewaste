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

public class DetailsPoubelleController {

    @FXML
    private TextField ResultatId;

    @FXML
    private TextField ResultatLocalisation;

    @FXML
    private TextField ResultatNiveauRemplissage;

    @FXML
    private TextField ResultatStatus;

    private int poubelleId; // Stocker l'ID de la poubelle

    private final ServicePoubelle servicePoubelle = new ServicePoubelle();

    /**
     * Initialise les champs avec les valeurs d'une poubelle.
     */
    public void setResultatId(int id) {
        this.poubelleId = id;
        ResultatId.setText(String.valueOf(id));
    }

    public void setResultatLocalisation(String localisation) {
        ResultatLocalisation.setText(localisation);
    }

    public void setResultatNiveauRemplissage(float niveau) {
        ResultatNiveauRemplissage.setText(String.valueOf(niveau));
    }

    public void setResultatStatus(String status) {
        ResultatStatus.setText(status);
    }

    /**
     * Envoie une alerte sur la poubelle.
     */
    @FXML
    void envoyerAlerte(ActionEvent event) {
        afficherAlerte("⚠️ Alerte", "Une alerte a été envoyée pour cette poubelle.");
    }

    /**
     * Met à jour le statut et le niveau de remplissage de la poubelle.
     */
    @FXML
    void mettreAJourStatus(ActionEvent event) {
        String localisation = ResultatLocalisation.getText();
        String niveauStr = ResultatNiveauRemplissage.getText();
        String status = ResultatStatus.getText();

        // Vérification des entrées
        if (localisation.isEmpty() || niveauStr.isEmpty() || status.isEmpty()) {
            afficherAlerte("⚠️ Erreur", "Veuillez remplir tous les champs.");
            return;
        }

        try {
            float niveau = Float.parseFloat(niveauStr);
            Poubelle poubelle = new Poubelle(poubelleId, localisation, niveau, status);
            servicePoubelle.modifier(poubelle);
            afficherAlerte("✅ Mise à jour", "Lala poubelle a été mis à jour.");
        } catch (NumberFormatException e) {
            afficherAlerte("⚠️ Erreur", "Le niveau de remplissage doit être un nombre valide.");
        }
    }

    /**
     * Supprime la poubelle actuelle.
     */
    @FXML
    void supprimerPoubelle(ActionEvent event) {
        if (poubelleId == 0) {
            afficherAlerte("⚠️ Erreur", "Aucune poubelle sélectionnée pour suppression.");
            return;
        }

        servicePoubelle.supprimer(poubelleId);
        afficherAlerte("🗑️ Suppression", "Poubelle supprimée avec succès.");
        retourListePoubelles(event);
    }

    /**
     * Retourne à la liste des poubelles.
     */
    @FXML
    void retourListePoubelles(ActionEvent event) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/ListPoubelle.fxml"));
            Parent root = loader.load();

            Stage stage = (Stage) ((javafx.scene.Node) event.getSource()).getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle("🗑️ Gestion des Poubelles");
            stage.show();
        } catch (IOException e) {
            afficherAlerte("⚠️ Erreur", "Impossible de retourner à la liste des poubelles.");
        }
    }

    /**
     * Affiche une alerte d'information.
     */
    private void afficherAlerte(String titre, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(titre);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
