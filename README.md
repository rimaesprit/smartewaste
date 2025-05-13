# Readme-guide
SmartWaste System 

# SmartWaste System – Symfony 6.4

Projet développé dans le cadre du module **PIDEV** à l'**Esprit School of Engineering**, visant à créer un système de gestion intelligente des déchets.

## 📚 Table des matières
- [Description](#description)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Technologies utilisées](#technologies-utilisées)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## 📌 Description

SmartWaste Web est une plateforme web développée avec Symfony 6.4 permettant :
- **Gestion des utilisateurs** : Administrateurs, collecteurs et citoyens.
- **Suivi des signalements et collectes** : Traçabilité complète des déchets.
- **Gestion des centres de traitement et conteneurs** : Optimisation des ressources.
- **Connexion intelligente** : Liaison entre citoyens et collecteurs via des capteurs IoT.

---

## ⚙️ Installation

1. **Cloner le projet** :
   
   git clone https://github.com/rimaesprit/smartewaste.git
   cd smartewaste

2.Configurer le fichier .env :

DATABASE_URL="mysql://root:@127.0.0.1:3306/smartewaste"

3.Installer les dépendances Symfony :
composer install

4.Démarrer le serveur Symfony :
symfony server:start
5.Accéder à l'application :
http://localhost:8000

## 🚀 Utilisation
Gestion des utilisateurs : Ajout, modification et suppression des rôles.

Consultation des signalements : Visualisation et traitement des requêtes.

Ajout des collecteurs, conteneurs et camions : Gestion des ressources.

Visualisation des statistiques de collecte : Tableaux de bord analytiques.

Gestion du bien-être : Suivi des indicateurs environnementaux.

## 💻 Technologies utilisées
Backend : PHP / Symfony 6.4

Frontend : Twig, Bootstrap

Base de données : Doctrine ORM, MySQL

## 🤝 Contribuer
Les contributions sont les bienvenues ! Voici comment procéder :

Forker le projet : Créez une copie du projet dans votre compte GitHub.

Cloner votre fork :
git clone https://github.com/votre-utilisateur/smartewaste.git

Créer une branche :
git checkout -b ma-nouvelle-fonctionnalite

Faire vos modifications : Ajoutez ou améliorez des fonctionnalités.

Pusher et créer une Pull Request : Soumettez vos changements pour revue.

📜 Licence
Ce projet est sous licence MIT. Pour plus de détails, consultez le fichier LICENSE.

 # SmartWaste – Application Java

Cette application Java fait partie du projet **SmartWaste System**, développé dans le cadre du module **PIDEV 3A** à **Esprit School of Engineering** (année universitaire 2024-2025). Elle permet aux collecteurs de gérer les collectes de déchets, consulter les signalements et interagir avec l’API de la plateforme Web.

---

## 📌 Description

L'application Java a été conçue pour :
- Faciliter le suivi des collectes de déchets par les agents de terrain.
- Permettre la connexion sécurisée des collecteurs via API.
- Afficher les signalements envoyés par les citoyens.
- Marquer une collecte comme effectuée.
- Accéder aux informations sur les poubelles, citoyens et centres.

---

## 🗂️ Table des matières

- [Installation](#installation)
- [Utilisation](#utilisation)
- [Technologies utilisées](#technologies-utilisées)
- [Contribuer](#contribuer)
- [Licence](#licence)

---

## ⚙️ Installation

1. Cloner le projet :

```bash
git clone https://github.com/rimaesprit/smartewaste.git
cd smartewaste/java-app
Ouvrir le dossier dans un IDE Java comme IntelliJ, Eclipse ou NetBeans.

Ajouter les bibliothèques nécessaires :

Gson (pour le traitement JSON)

OkHttp ou HttpClient (pour les requêtes HTTP)

JDBC (pour la connexion à MySQL si utilisée en local)

Lancer la compilation et exécuter la classe principale.


---

## Utilisation :
Connexion du collecteur via identifiants API.

Liste des signalements et détails de chaque collecte.

Mise à jour de l’état des poubelles.

Visualisation des centres et citoyens associés.

Interface simple et adaptée au travail de terrain.

## 🧰 Technologies utilisées
Java 17

Swing (ou JavaFX) pour l'interface graphique

Gson pour le parsing JSON

OkHttp / HttpURLConnection pour les requêtes API

REST API (provenant de l’application Symfony)

MySQL (optionnel si persistance locale)

##  🤝 Contribuer
Les contributions sont les bienvenues !
Voici comment faire :

Fork le projet.

Crée une branche (feature/NouvelleFonctionnalite).

Commit tes modifications.

Push vers ta branche.

Crée une Pull Request pour révision.
---

## 📄 Licence
Ce projet est distribué sous la licence MIT – voir le fichier LICENSE pour plus d’informations.
---

## 🙌 Remerciements
Projet réalisé dans le cadre du PIDEV à l’Esprit School of Engineering
Par : Oumayma Bahbaa, Ryma jenhani, Chayma ben salah, Fedi arbi, Zyed Sghaier
Encadré par : [Mme Rym Douss

]
