package com.esprit.controllers;

import com.esprit.models.bienetre;
import com.esprit.services.ServiceBienetre;
import javafx.collections.FXCollections;
import javafx.collections.ObservableList;
import javafx.event.ActionEvent;
import javafx.fxml.FXML;
import javafx.fxml.FXMLLoader;
import javafx.scene.Parent;
import javafx.scene.control.Alert;
import javafx.scene.control.TableColumn;
import javafx.scene.control.TableView;
import javafx.scene.control.cell.PropertyValueFactory;

import java.io.IOException;
import java.util.List;

public class ListBienetreController {

    @FXML
    private TableView<bienetre> TableViewListBienetre;

    @FXML
    private TableColumn<bienetre, String> ColumnNom;

    @FXML
    private TableColumn<bienetre, String> ColumnReview;

    @FXML
    private TableColumn<bienetre, Double> ColumnFidality;

    @FXML
    private TableColumn<bienetre, Double> ColumnRate;

    @FXML
    void afficherBienetres() {
        try {
            ServiceBienetre serviceBienetre = new ServiceBienetre();
            List<bienetre> bienetreList = serviceBienetre.afficher();
            ObservableList<bienetre> observableBienetreList = FXCollections.observableArrayList(bienetreList);

            ColumnNom.setCellValueFactory(new PropertyValueFactory<>("nom"));
            ColumnReview.setCellValueFactory(new PropertyValueFactory<>("review"));
            ColumnFidality.setCellValueFactory(new PropertyValueFactory<>("fidality"));
            ColumnRate.setCellValueFactory(new PropertyValueFactory<>("rate"));

            TableViewListBienetre.setItems(observableBienetreList);
        } catch (Exception e) {
            afficherAlerte("❌ Erreur", "Impossible d'afficher les bien-êtres");
        }
    }

    @FXML
    public void initialize() {
        afficherBienetres();

        TableViewListBienetre.setOnMouseClicked(event -> {
            if (event.getClickCount() == 2) {
                bienetre selectedBienetre = TableViewListBienetre.getSelectionModel().getSelectedItem();
                if (selectedBienetre != null) {
                    ouvrirDetailsBienetre(selectedBienetre);
                }
            }
        });
    }

    private void ouvrirDetailsBienetre(bienetre bienetre) {
        try {
            FXMLLoader loader = new FXMLLoader(getClass().getResource("/DetailsBienetre.fxml"));
            Parent root = loader.load();

            DetailsBienetreController dbc = loader.getController();
            dbc.setDetailsBienetre(
                    bienetre.getNom(),
                    bienetre.getReview(),
                    bienetre.getFidality(),
                    bienetre.getRate()
            );

            TableViewListBienetre.getScene().setRoot(root);
        } catch (IOException e) {
            afficherAlerte("⚠️ Erreur", "Impossible d'ouvrir les détails");
        }
    }

    @FXML
    void ButtonActionAjouterBienetre(ActionEvent event) {
        changerScene("/AjouterBienetre.fxml", "➕ Ajouter un Bien-être");
    }

    @FXML
    void ButtonActionRetourMenu(ActionEvent event) {
        changerScene("/Home.fxml", "🏡 Menu Principal");
    }

    private void changerScene(String cheminFXML, String titre) {
        try {
            Parent root = FXMLLoader.load(getClass().getResource(cheminFXML));
            TableViewListBienetre.getScene().setRoot(root);
        } catch (IOException e) {
            afficherAlerte("⚠️ Erreur", "Impossible de charger l'interface");
        }
    }

    private void afficherAlerte(String titre, String message) {
        Alert alert = new Alert(Alert.AlertType.INFORMATION);
        alert.setTitle(titre);
        alert.setHeaderText(null);
        alert.setContentText(message);
        alert.showAndWait();
    }
}
