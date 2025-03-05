package com.esprit.utils;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.SQLException;

public class database {
    private Connection connection;
    private static database instance;

    private final String URL = "jdbc:mysql://localhost:3306/chayma2";
    private final String USER = "root";
    private final String PASSWORD = "";

    private database() {
        try {
            connection = DriverManager.getConnection(URL,USER,PASSWORD);
            System.out.println("Connected to database");
        } catch (SQLException e) {
            System.out.println(e.getMessage());
        }
    }

    public static database getInstance() {
        if (instance == null)
            instance = new database(); // creer une instance si il n'y a aucune instance
        return instance;
    }

    public Connection getConnection() {
        return connection;
    }
}
