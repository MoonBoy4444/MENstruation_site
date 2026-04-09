# Documentation Technique

## 1. Nature de l application

L application MENstruation est une application lourde locale de type boutique e-commerce.

Elle repond au besoin par:

- un backend Python local
- une base SQLite embarquee
- une interface web servie localement
- une logique metier complete autour des comptes, commandes, avis et administration

## 2. Fonctionnalites principales

### Authentification

- connexion client
- inscription client
- conservation de session dans le navigateur

### Catalogue

- listing produits
- filtres par categorie, gamme et coloris
- fiche produit detaillee
- avis lies au compte client

### Commande

- panier persistant
- paiement
- adresse de livraison
- creation de commande et lignes de commande
- suivi de commande jusqu a l arrivee chez le client

### Profil

- edition des informations personnelles
- consultation des adresses
- consultation des avis publies

### Back-office

- tableau de bord admin
- vue clients
- vue stock
- ajout et modification de produits

## 3. API locale

### Auth

- `POST /api/auth/login`
- `POST /api/auth/register`

### Catalogue

- `GET /api/home`
- `GET /api/catalog`
- `GET /api/products/:id`
- `POST /api/reviews`

### Profil et commandes

- `GET /api/profile/:id`
- `PUT /api/profile/:id`
- `GET /api/orders/:id`
- `POST /api/orders`

### Administration

- `GET /api/admin/dashboard`
- `GET /api/admin/clients`
- `GET /api/admin/products`
- `POST /api/admin/products`
- `PUT /api/admin/products`

## 4. Base de donnees

Les entites principales sont:

- clients
- types de clients
- produits
- types de produits
- commandes
- lignes de commande
- paiements
- livraisons
- adresses
- avis

## 5. Donnees de demonstration

Le projet contient:

- des comptes de demonstration
- un catalogue de demonstration
- des commandes existantes
- des avis existants

Ces donnees permettent de montrer les cas d usage sans saisie complete manuelle.

## 6. Lancement

```bash
python3 app.py
```

Puis ouvrir l URL affichee dans le terminal.

## 7. Points de qualite

- separation claire frontend / backend / donnees
- pas de dependance a un outil de build frontend
- schema SQL versionne dans le projet
- documents d architecture, de depot et de traçabilite
