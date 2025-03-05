package com.esprit.models;

public class Poubelle {
    private int id;
    private String localisation;
    private float niveauRemplissage;
    private String status;

    // Constructeur sans id (pour l'ajout)
    public Poubelle(String localisation, float niveauRemplissage, String status) {
        this.localisation = localisation;
        this.niveauRemplissage = niveauRemplissage;
        this.status = status;
    }

    // Constructeur avec id (pour la modification et l'affichage)
    public Poubelle(int id, String localisation, float niveauRemplissage, String status) {
        this.id = id;
        this.localisation = localisation;
        this.niveauRemplissage = niveauRemplissage;
        this.status = status;
    }

    // Getters et Setters
    public int getId() {
        return id;
    }

    public String getLocalisation() {
        return localisation;
    }

    public void setLocalisation(String localisation) {
        this.localisation = localisation;
    }

    public float getNiveauRemplissage() {
        return niveauRemplissage;
    }

    public void setNiveauRemplissage(float niveauRemplissage) {
        this.niveauRemplissage = niveauRemplissage;
    }

    public String getStatus() {
        return status;
    }

    public void setStatus(String status) {
        this.status = status;
    }
}
