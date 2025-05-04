# SmartWaste - Système de Gestion Intelligente des Déchets

SmartWaste est une application complète de gestion des déchets qui permet de suivre, analyser et optimiser la collecte et le traitement des déchets. Le système est composé d'une interface publique et d'une interface d'administration avec des fonctionnalités avancées.

## Fonctionnalités principales

### Gestion des Camions

1. **Gestion de base**
   - Liste, création, édition et suppression des camions
   - Suivi de l'état des camions (en service, en maintenance, hors service)

2. **Fonctionnalités métier avancées**
   - **Activation/désactivation** - Changement d'état des camions avec conséquences sur leur disponibilité
   - **Géolocalisation** - API de suivi en temps réel des positions des camions
   - **Statistiques** - Tableaux de bord et graphiques d'analyse de la flotte
   - **Notifications** - Système d'alertes pour les conducteurs
   - **Recherche avancée** - Filtrage multicritères des camions
   - **Optimisation des trajets** - Calcul des itinéraires optimaux pour la collecte
   - **Comparaison environnementale** - Analyse et comparaison de l'impact écologique des camions

### Gestion des Déchets

1. **Gestion de base**
   - Liste, création, édition et suppression des déchets
   - Assignation des déchets aux camions

2. **Fonctionnalités métier avancées**
   - **Calendrier** - Visualisation des dépôts de déchets sur un calendrier interactif
   - **Prédiction IA** - Algorithmes d'intelligence artificielle pour anticiper les collectes futures
   - **Système de favoris** - Marquage de certains déchets comme favoris
   - **Recherche multicritères** - Filtrage avancé des déchets
   - **Traitement par lot** - Panier de sélection pour traiter des déchets en masse
   - **Historique et analyse** - Tableaux de bord et graphiques d'analyse des déchets collectés

## Routes de l'application

### Interface publique

- **/** - Page d'accueil
- **/about** - À propos de SmartWaste
- **/services** - Description des services offerts
- **/contact** - Formulaire de contact
- **/map** - Carte des collectes en temps réel
- **/admin** - Redirection vers l'interface d'administration

### Interface d'administration (Camions)

- **/admin/camion/** - Liste des camions
- **/admin/camion/new** - Ajout d'un nouveau camion
- **/admin/camion/{id}** - Détails d'un camion
- **/admin/camion/{id}/edit** - Édition d'un camion
- **/admin/camion/{id}/toggle-status** - Activer/désactiver un camion
- **/admin/camion/{id}/position** - API de géolocalisation
- **/admin/camion/statistics/dashboard** - Tableau de bord statistique
- **/admin/camion/{id}/alert** - Envoi d'alertes
- **/admin/camion/search** - Recherche avancée
- **/admin/camion/optimisation** - Optimisation des trajets
- **/admin/camion/eco-compare** - Comparaison environnementale

### Interface d'administration (Déchets)

- **/admin/dechet/** - Liste des déchets
- **/admin/dechet/new** - Ajout d'un nouveau déchet
- **/admin/dechet/{id}** - Détails d'un déchet
- **/admin/dechet/{id}/edit** - Édition d'un déchet
- **/admin/dechet/calendar/{id}** - Calendrier des dépôts
- **/admin/dechet/prediction** - Prédictions IA
- **/admin/dechet/{id}/toggle-favorite** - Marquer comme favori
- **/admin/dechet/search** - Recherche multicritères
- **/admin/dechet/batch/process** - Traitement par lot
- **/admin/dechet/batch/assign** - Assignation par lot
- **/admin/dechet/historique-analyse** - Historique et analyses

## APIs

- **/api/camions** - Données de géolocalisation des camions
- **/api/statistics** - Statistiques générales des déchets

## Installation

1. Cloner le dépôt
```bash
git clone https://github.com/votre-username/smartwaste.git
cd smartwaste
```

2. Installer les dépendances
```bash
composer install
```

3. Configurer la base de données dans le fichier .env

4. Créer la base de données et exécuter les migrations
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Charger les données de test (facultatif)
```bash
php bin/console doctrine:fixtures:load
```

6. Lancer le serveur
```bash
symfony serve
```

## Technologies utilisées

- **Frontend** : HTML, CSS, JavaScript, Bootstrap, Chart.js, Leaflet.js
- **Backend** : PHP, Symfony 6
- **Base de données** : MySQL/MariaDB
- **Autres** : FullCalendar, Tippy.js 

## Nettoyer le cache

```bash
php bin/console cache:clear
``` 