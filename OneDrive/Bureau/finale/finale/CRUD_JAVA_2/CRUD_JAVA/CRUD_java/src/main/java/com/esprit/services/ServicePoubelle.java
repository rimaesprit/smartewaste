package com.esprit.services;

import com.esprit.models.Poubelle;
import com.esprit.utils.database;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ServicePoubelle {
    private Connection connection;

    public ServicePoubelle() {
        connection = database.getInstance().getConnection();
    }

    // Vérifier si une poubelle avec la même localisation existe déjà
    public boolean existePoubelleAvecLocalisation(String localisation) {
        String req = "SELECT COUNT(*) FROM poubelle WHERE localisation = ?";
        try (PreparedStatement ps = connection.prepareStatement(req)) {
            ps.setString(1, localisation);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    int count = rs.getInt(1);
                    return count > 0; // Si le nombre de résultats est supérieur à 0, la localisation existe déjà
                }
            }
        } catch (SQLException e) {
            System.out.println("❌ Erreur lors de la vérification de la localisation : " + e.getMessage());
        }
        return false; // Si aucune poubelle avec cette localisation n'existe
    }

    // Ajouter une poubelle (id auto-incrémenté)
    public void ajouter(Poubelle poubelle) {
        String req = "INSERT INTO poubelle (localisation, niveauRemplissage, status) VALUES (?, ?, ?)";
        try (PreparedStatement ps = connection.prepareStatement(req)) {
            ps.setString(1, poubelle.getLocalisation());
            ps.setFloat(2, poubelle.getNiveauRemplissage());
            ps.setString(3, poubelle.getStatus());
            ps.executeUpdate();
            System.out.println("✅ Poubelle ajoutée avec succès !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur lors de l'ajout : " + e.getMessage());
        }
    }

    // Modifier une poubelle
    public void modifier(Poubelle poubelle) {
        String req = "UPDATE poubelle SET localisation = ?, niveauRemplissage = ?, status = ? WHERE id = ?";
        try (PreparedStatement ps = connection.prepareStatement(req)) {
            ps.setString(1, poubelle.getLocalisation());
            ps.setFloat(2, poubelle.getNiveauRemplissage());
            ps.setString(3, poubelle.getStatus());
            ps.setInt(4, poubelle.getId());
            ps.executeUpdate();
            System.out.println("✅ Poubelle modifiée avec succès !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur lors de la modification : " + e.getMessage());
        }
    }

    // Supprimer une poubelle
    public void supprimer(int id) {
        String req = "DELETE FROM poubelle WHERE id = ?";
        try (PreparedStatement ps = connection.prepareStatement(req)) {
            ps.setInt(1, id);
            ps.executeUpdate();
            System.out.println("✅ Poubelle supprimée avec succès !");
        } catch (SQLException e) {
            System.out.println("❌ Erreur lors de la suppression : " + e.getMessage());
        }
    }

    // Afficher toutes les poubelles
    public List<Poubelle> afficher() {
        List<Poubelle> poubelles = new ArrayList<>();
        String req = "SELECT * FROM poubelle";
        try (Statement st = connection.createStatement(); ResultSet rs = st.executeQuery(req)) {
            while (rs.next()) {
                Poubelle p = new Poubelle(
                        rs.getInt("id"),
                        rs.getString("localisation"),
                        rs.getFloat("niveauRemplissage"),
                        rs.getString("status")
                );
                poubelles.add(p);
            }
        } catch (SQLException e) {
            System.out.println("❌ Erreur lors de l'affichage : " + e.getMessage());
        }
        return poubelles;
    }
}
