# Traceabilite

## Objectif

Ce document fait le lien entre les exigences attendues et leur implementation dans l application MENstruation.

## Matrice de traceabilite

### 1. Base de donnees faite

- Statut: conforme
- Preuves:
  - [schema.sql](/Users/user/Desktop/MENstruations/database/schema.sql)
  - [seed.sql](/Users/user/Desktop/MENstruations/database/seed.sql)
  - [database.py](/Users/user/Desktop/MENstruations/backend/database.py)
- Couvre:
  - clients
  - produits
  - commandes
  - livraisons
  - paiements
  - avis

### 2. Connexion possible

- Statut: conforme
- Preuves:
  - [repositories.py](/Users/user/Desktop/MENstruations/backend/repositories.py)
  - [authViewModel.js](/Users/user/Desktop/MENstruations/static/js/viewmodels/authViewModel.js)
  - [server.py](/Users/user/Desktop/MENstruations/backend/server.py)
- Couvre:
  - login
  - inscription
  - session locale

### 3. Back-office fait

- Statut: conforme
- Preuves:
  - [AdminRepository](/Users/user/Desktop/MENstruations/backend/repositories.py)
  - [adminViewModel.js](/Users/user/Desktop/MENstruations/static/js/viewmodels/adminViewModel.js)
  - [views.js](/Users/user/Desktop/MENstruations/static/js/views.js)
- Couvre:
  - statistiques
  - clients
  - produits
  - edition catalogue

### 4. Bonne documentation

- Statut: renforce
- Preuves:
  - [README.md](/Users/user/Desktop/MENstruations/README.md)
  - [architecture.md](/Users/user/Desktop/MENstruations/docs/architecture.md)
  - [documentation-technique.md](/Users/user/Desktop/MENstruations/docs/documentation-technique.md)

### 5. Bonne tracabilite

- Statut: renforce
- Preuves:
  - [traceabilite.md](/Users/user/Desktop/MENstruations/docs/traceabilite.md)
- Couvre:
  - correspondance exigence / implementation
  - preuves par fichiers

### 6. Bon depot

- Statut: renforce
- Preuves:
  - [depot.md](/Users/user/Desktop/MENstruations/docs/depot.md)
  - [.gitignore](/Users/user/Desktop/MENstruations/.gitignore)
- Couvre:
  - structure claire
  - depot Git local initialise
  - exclusions de fichiers caches

## Validation fonctionnelle minimale

Les parcours a verifier sont:

1. lancer le serveur
2. se connecter avec un compte de demonstration
3. consulter le catalogue
4. ajouter au panier
5. creer une commande
6. consulter le suivi de commande
7. ouvrir le back-office avec le compte admin
