package com.esprit.models;

public class bienetre {
    private String nom;
    private String review;
    private double rate; // Remplace "sentiment" par la note prédite
    private double fidality;

    // ✅ Constructeur
    public bienetre(String nom, String review, double fidality) {
        this.nom = nom;
        this.review = review;
        this.fidality = fidality;
        this.rate = -1; // Par défaut, en attendant la prédiction
    }

    public bienetre(String nom, String review, double rate, double fidality) {
        this.nom = nom;
        this.review = review;
        this.rate = rate;
        this.fidality = fidality;
    }

    // ✅ Getters
    public String getNom() {
        return nom;
    }

    public String getReview() {
        return review;
    }

    public double getRate() {
        return rate;
    }

    public double getFidality() {
        return fidality;
    }

    // ✅ Setters
    public void setNom(String nom) {
        this.nom = nom;
    }

    public void setReview(String review) {
        this.review = review;
    }

    public void setRate(double rate) {
        this.rate = rate;
    }

    public void setFidality(double fidality) {
        this.fidality = fidality;
    }

    @Override
    public String toString() {
        return "bienetre{" +
                "nom='" + nom + '\'' +
                ", review='" + review + '\'' +
                ", rate=" + rate +
                ", fidality=" + fidality +
                '}';
    }
}
