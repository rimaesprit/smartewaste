package com.esprit.services;

import com.esprit.models.bienetre;
import com.esprit.utils.database;
import org.json.JSONObject;

import java.io.*;
import java.net.HttpURLConnection;
import java.net.URL;
import java.nio.charset.StandardCharsets;
import java.sql.*;
import java.util.ArrayList;
import java.util.List;

public class ServiceBienetre {
    private Connection connection;

    public ServiceBienetre() {
        connection = database.getInstance().getConnection();
    }

    /**
     * Méthode pour prédire la note d'un avis en fonction du texte et de la fidélité via Flask
     *
     * @param review   - Texte de l'avis
     * @param fidality - Fidélité du client (entre 0 et 1)
     * @return Note prédite entre 0 et 5, ou -1 en cas d'erreur
     */
    public double predireNote(String review, double fidality) {
        if (review == null || review.trim().isEmpty()) {
            return -1;
        }

        HttpURLConnection conn = null;
        try {
            URL url = new URL("http://localhost:5001/predict");
            conn = (HttpURLConnection) url.openConnection();
            conn.setRequestMethod("POST");
            conn.setRequestProperty("Content-Type", "application/json");
            conn.setDoOutput(true);

            // Création du JSON avec review et fidality
            JSONObject jsonInput = new JSONObject();
            jsonInput.put("review", review);
            jsonInput.put("fidality", fidality);

            // Envoi de la requête
            try (OutputStream os = conn.getOutputStream()) {
                byte[] input = jsonInput.toString().getBytes(StandardCharsets.UTF_8);
                os.write(input, 0, input.length);
            }

            // Lecture de la réponse
            StringBuilder response = new StringBuilder();
            try (BufferedReader br = new BufferedReader(new InputStreamReader(conn.getInputStream(), StandardCharsets.UTF_8))) {
                String responseLine;
                while ((responseLine = br.readLine()) != null) {
                    response.append(responseLine.trim());
                }
            }

            // Extraction de la note prédite
            JSONObject jsonResponse = new JSONObject(response.toString());
            return jsonResponse.optDouble("predicted_rating", -1);

        } catch (Exception e) {
            System.err.println("❌ Erreur lors de la prédiction de la note : " + e.getMessage());
            e.printStackTrace();
            return -1;
        } finally {
            if (conn != null) {
                conn.disconnect();
            }
        }
    }

    /**
     * Ajouter un bien-être avec prédiction de la note
     *
     * @param bienetre - Objet bienetre contenant review et fidality
     */
    public void ajouter(bienetre bienetre) {
        double predictedNote = predireNote(bienetre.getReview(), bienetre.getFidality()); // Prédiction

        String req = "INSERT INTO bienetre (nom, review, rate, fidality) VALUES (?, ?, ?, ?)";

        try (PreparedStatement ps = connection.prepareStatement(req)) {
            ps.setString(1, bienetre.getNom());
            ps.setString(2, bienetre.getReview());
            ps.setDouble(3, predictedNote); // Stocker la note prédite
            ps.setDouble(4, bienetre.getFidality());

            ps.executeUpdate();
            System.out.println("✅ Ajout réussi : " + bienetre.getNom() + " (Note prédite : " + predictedNote + ")");
        } catch (SQLException e) {
            System.err.println("❌ Erreur lors de l'ajout : " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Modifier un bien-être existant
     *
     * @param bienetre - Objet bienetre contenant review et fidality
     */
    public void modifier(bienetre bienetre) {
        String req = "UPDATE bienetre SET review = ?, fidality = ? WHERE nom = ?"; // ✅ Supprime la modification de rate

        try (PreparedStatement ps = connection.prepareStatement(req)) {
            ps.setString(1, bienetre.getReview());
            ps.setDouble(2, bienetre.getFidality());
            ps.setString(3, bienetre.getNom());

            ps.executeUpdate();
            System.out.println("✅ Modification réussie : " + bienetre.getNom() + " (Fidélité mise à jour)");
        } catch (SQLException e) {
            System.err.println("❌ Erreur lors de la modification : " + e.getMessage());
            e.printStackTrace();
        }
    }

    /**
     * Supprimer un bien-être par son nom
     *
     * @param nom - Nom du bien-être à supprimer
     */
    public boolean supprimer(String nom) {
        String req = "DELETE FROM bienetre WHERE nom = ?";

        try (PreparedStatement ps = connection.prepareStatement(req)) {
            ps.setString(1, nom);
            int rowsAffected = ps.executeUpdate();

            return rowsAffected > 0; // ✅ Returns true if at least one row was deleted
        } catch (SQLException e) {
            System.err.println("❌ Error while deleting: " + e.getMessage());
            e.printStackTrace();
            return false; // ✅ Returns false if an error occurs
        }
    }

    /**
     * Récupérer tous les bien-êtres enregistrés
     *
     * @return Liste d'objets bienetre
     */
    public List<bienetre> afficher() {
        List<bienetre> bienetreList = new ArrayList<>();
        String req = "SELECT nom, review, fidality, rate FROM bienetre";

        try (Statement st = connection.createStatement();
             ResultSet rs = st.executeQuery(req)) {

            while (rs.next()) {
                bienetre b = new bienetre(
                        rs.getString("nom"),
                        rs.getString("review"),
                        rs.getDouble("rate") ,
                        rs.getDouble("fidality") // ✅ Corrigé pour récupérer la fidélité correctement
                );
                bienetreList.add(b);
            }
        } catch (SQLException e) {
            System.err.println("❌ Erreur lors de l'affichage : " + e.getMessage());
            e.printStackTrace();
        }
        return bienetreList;
    }
}
