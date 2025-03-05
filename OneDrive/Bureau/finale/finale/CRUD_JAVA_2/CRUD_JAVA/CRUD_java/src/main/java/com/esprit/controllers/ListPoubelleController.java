package com.esprit.controllers;

import com.esprit.models.Poubelle;
import com.esprit.services.ServicePoubelle;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.Scene;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.cell.PropertyValueFactory;
import javafx.scene.control.Alert;
import javafx.scene.control.Alert.AlertType;
import javafx.stage.Stage;
import java.io.IOException;
import java.util.List;

public class ListPoubelleController {

    @FXML
    private TableColumn<Poubelle, Integer> ColumnId;

    @FXML
    private TableColumn<Poubelle, String> ColumnLocalisation;

    @FXML
    private TableColumn<Poubelle, Float> ColumnNiveauRemplissage;

    @FXML
    private TableColumn<Poubelle, String> ColumnStatus;

    @FXML
    private TableView<Poubelle> TableViewListPoubelle;

    /**
     * Initialise les données de la TableView.
     */
    @FXML
    public void initialize() {
        afficherPoubelles();

        // Double-clic sur une ligne pour ouvrir les détails
        TableViewListPoubelle.setOnMouseClicked(event -> {
            if (event.getClickCount() == 2) {
                Poubelle selectedPoubelle = TableViewListPoubelle.getSelectionModel().getSelectedItem();
                if (selectedPoubelle != null) {
                    ouvrirDetailsPoubelle(selectedPoubelle);
                }
            }
        });
    }

    /**
     * Charge et affiche les données dans la TableView.
     */
    @FXML
    void afficherPoubelles() {
        try {
            ServicePoubelle servicePoubelle = new ServicePoubelle();
            List<Poubelle> poubelleList = servicePoubelle.afficher();
            ObservableList<Poubelle> observableList = FXCollections.observableArrayList(poubelleList);

            // Liaison des colonnes avec les attributs de l'objet Poubelle
            ColumnId.setCellValueFactory(new PropertyValueFactory<>("id"));
            ColumnLocalisation.setCellValueFactory(new PropertyValueFactory<>("localisation"));
            ColumnNiveauRemplissage.setCellValueFactory(new PropertyValueFactory<>("niveauRemplissage"));
            ColumnStatus.setCellValueFactory(new PropertyValueFactory<>("status"));

            TableViewListPoubelle.setItems(observableList);
        } catch (Exception e) {
            afficherAlerte("❌ Erreur", "Impossible d'afficher les poubelles", e.getMessage());
        }
    }

    /**
     * Ouvre l'interface pour ajouter une nouvelle poubelle.
     */
    @FXML
    void ButtonActionAjouterPoubelle(ActionEvent event) {
        changerScene("/AjouterPoubelle.fxml", "➕ Ajouter une Poubelle");
    }

    /**
     * Ouvre l'interface de détails d'une poubelle sélectionnée.
     */
    private void ouvrirDetailsPoubelle(Poubelle poubelle) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/DetailsPoubelle.fxml"));
            Parent root = loader.load();

            DetailsPoubelleController dpc = loader.getController();
            dpc.setResultatId(poubelle.getId());
            dpc.setResultatLocalisation(poubelle.getLocalisation());
            dpc.setResultatNiveauRemplissage(poubelle.getNiveauRemplissage());
            dpc.setResultatStatus(poubelle.getStatus());

            // Changer de scène
            Stage stage = (Stage) TableViewListPoubelle.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.show();
        } catch (IOException e) {
            afficherAlerte("⚠️ Erreur d'ouverture", "Impossible d'ouvrir les détails", e.getMessage());
        }
    }

    /**
     * Retourne au menu principal.
     */
    @FXML
    void ButtonActionRetourMenu(ActionEvent event) {
        changerScene("/Home.fxml", "🏡 Menu Principal");
    }

    /**
     * Change de scène vers un fichier FXML donné.
     */
    private void changerScene(String fxmlPath, String titre) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource(fxmlPath));
            Parent root = loader.load();

            Stage stage = (Stage) TableViewListPoubelle.getScene().getWindow();
            stage.setScene(new Scene(root));
            stage.setTitle(titre);
            stage.show();
        } catch (IOException e) {
            afficherAlerte("⚠️ Erreur de navigation", "Impossible de charger l'interface", e.getMessage());
        }
    }

    /**
     * Affiche une alerte d'erreur ou d'information.
     */
    private void afficherAlerte(String titre, String enTete, String contenu) {
        Alert alert = new Alert(AlertType.ERROR);
        alert.setTitle(titre);
        alert.setHeaderText(enTete);
        alert.setContentText(contenu);
        alert.showAndWait();
    }
}
