package com.esprit.services;

public interface CrudService <T> {
    void ajouter(T t);
    void modifier (T t);
    void supprimer (int id);
}
