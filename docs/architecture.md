# Architecture MVVM

## Vue d ensemble

MENstruation est une application lourde locale au sens fonctionnel:

- elle embarque son backend
- elle embarque sa base de donnees
- elle sert son interface localement
- elle gere des workflows complets de compte, commande, avis et administration

L architecture suit une logique `MVVM`.

## Backend

- [database.py](/Users/user/Desktop/MENstruations/backend/database.py)
  initialise la base SQLite, gere les connexions et reconstruit la base si le schema attendu a evolue.
- [repositories.py](/Users/user/Desktop/MENstruations/backend/repositories.py)
  contient l acces aux donnees et la logique proche des tables.
- [services.py](/Users/user/Desktop/MENstruations/backend/services.py)
  assemble les donnees pour les vues et applique la logique metier de haut niveau.
- [server.py](/Users/user/Desktop/MENstruations/backend/server.py)
  expose l API HTTP JSON et sert les fichiers statiques.

## Frontend

- [index.html](/Users/user/Desktop/MENstruations/static/index.html)
  structure principale de l application.
- [styles.css](/Users/user/Desktop/MENstruations/static/styles.css)
  theme visuel, responsivite et ergonomie.
- [views.js](/Users/user/Desktop/MENstruations/static/js/views.js)
  generation des vues HTML.
- [app.js](/Users/user/Desktop/MENstruations/static/js/app.js)
  routage, rendu global et branchement des interactions.
- `viewmodels/*.js`
  logique de presentation par module fonctionnel.
- [api.js](/Users/user/Desktop/MENstruations/static/js/core/api.js)
  client HTTP du frontend.
- [store.js](/Users/user/Desktop/MENstruations/static/js/core/store.js)
  etat applicatif, session et panier.

## Correspondance MVVM

- `Model`
  SQLite, schema SQL, seed SQL, repositories, services
- `ViewModel`
  `static/js/viewmodels/*.js`
- `View`
  `static/index.html`, `static/styles.css`, `static/js/views.js`

## Modules fonctionnels

- authentification et inscription
- catalogue, filtres et fiche produit
- panier et checkout
- profil client, adresses et avis
- commandes, livraisons et suivi
- administration

## Flux principal de commande

1. Le client se connecte ou cree un compte.
2. Il consulte le catalogue et ajoute des produits au panier.
3. Il choisit un mode de paiement et renseigne son adresse.
4. La commande, la livraison et les lignes de commande sont enregistrees en base.
5. La page `Commandes` affiche l historique et le suivi de livraison.

## Donnees persistantes

La base de donnees est locale et stockee dans:

- [gamerdry.sqlite3](/Users/user/Desktop/MENstruations/database/gamerdry.sqlite3)

Elle est alimentee par:

- [schema.sql](/Users/user/Desktop/MENstruations/database/schema.sql)
- [seed.sql](/Users/user/Desktop/MENstruations/database/seed.sql)
