# Architecture MVVM

## Vue d ensemble

MENstruation est une application lourde locale au sens fonctionnel:

- elle embarque son backend
- elle embarque sa base de donnees
- elle sert son interface localement
- elle gere des workflows complets de compte, commande, avis et administration

L architecture suit une logique `MVVM`.

## Backend

- [src/Controller/ApiController.php](/Users/user/Desktop/MENstruations/MENstruation_site/src/Controller/ApiController.php)
  expose l API JSON de la boutique.
- [src/Controller/SiteController.php](/Users/user/Desktop/MENstruations/MENstruation_site/src/Controller/SiteController.php)
  sert la coque HTML principale du site.
- [src/Service/ShopService.php](/Users/user/Desktop/MENstruations/MENstruation_site/src/Service/ShopService.php)
  regroupe la logique metier.
- [src/Infrastructure/SqliteStore.php](/Users/user/Desktop/MENstruations/MENstruation_site/src/Infrastructure/SqliteStore.php)
  initialise et fournit la connexion SQLite.

## Frontend

- [app-shell.html](/Users/user/Desktop/MENstruations/MENstruation_site/public/app-shell.html)
  structure principale de l application.
- [styles.css](/Users/user/Desktop/MENstruations/MENstruation_site/public/styles.css)
  theme visuel, responsivite et ergonomie.
- [views.js](/Users/user/Desktop/MENstruations/MENstruation_site/public/js/views.js)
  generation des vues HTML.
- [app.js](/Users/user/Desktop/MENstruations/MENstruation_site/public/js/app.js)
  routage, rendu global et branchement des interactions.
- `viewmodels/*.js`
  logique de presentation par module fonctionnel.
- [api.js](/Users/user/Desktop/MENstruations/MENstruation_site/public/js/core/api.js)
  client HTTP du frontend.
- [store.js](/Users/user/Desktop/MENstruations/MENstruation_site/public/js/core/store.js)
  etat applicatif, session et panier.

## Correspondance MVVM

- `Model`
  SQLite, schema SQL, seed SQL, services et infrastructure
- `ViewModel`
  `public/js/viewmodels/*.js`
- `View`
  `public/app-shell.html`, `public/styles.css`, `public/js/views.js`

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

- [gamerdry.sqlite3](/Users/user/Desktop/MENstruations/MENstruation_site/database/gamerdry.sqlite3)

Elle est alimentee par:

- [schema.sql](/Users/user/Desktop/MENstruations/MENstruation_site/database/schema.sql)
- [seed.sql](/Users/user/Desktop/MENstruations/MENstruation_site/database/seed.sql)
