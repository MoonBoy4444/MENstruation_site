# MENstruation

MENstruation est une boutique e-commerce locale construite avec Symfony et SQLite.
Le point d'entree officiel est `public/index.php`.

## Fonctionnalites

- catalogue avec filtres, fiches produit et avis
- connexion et inscription client
- profil client avec adresses, statistiques et avis
- panier, paiement, livraison et creation de commande
- historique de commandes avec suivi
- espace administrateur pour consulter les clients et gerer le catalogue

## Stack

- Symfony 8
- PHP 8.4 ou plus recent
- SQLite
- HTML, CSS et JavaScript modulaire

## Structure utile

- `public/` : front controller, assets et application frontend
- `src/Controller/` : routes web et API
- `src/Service/ShopService.php` : logique metier principale
- `src/Infrastructure/SqliteStore.php` : acces SQLite et initialisation de la base
- `database/` : schema SQL, seed SQL et base locale
- `router.php` : routeur de compatibilite pour `php -S`

## Installation

```bash
composer install
```

Extensions PHP attendues :

- `pdo_sqlite`
- `sqlite3`
- `ctype`
- `iconv`

## Lancer le site

### Option 1 : serveur PHP integre

Depuis la racine du projet :

```bash
php -S 127.0.0.1:8000 -t public router.php
```

URL :

```text
http://127.0.0.1:8000/
```

### Option 2 : WAMP / Apache sous Windows

Le projet peut fonctionner de deux manieres :

1. Le plus propre : creer un virtual host qui pointe directement vers le dossier `public/`.
2. Le plus simple : placer le projet dans `www/` puis ouvrir la racine du projet :

```text
http://localhost/MENstruation_site/
```

La racine du projet redirige automatiquement vers `public/`.

Points importants pour WAMP :

- activer `mod_rewrite`
- activer SQLite (`pdo_sqlite` et `sqlite3`)
- utiliser une version de PHP compatible avec Symfony 8, donc PHP 8.4+

## Verification Symfony

```bash
php bin/console about
php bin/console debug:router
php bin/console cache:clear
```

## Comptes de demonstration

- Admin : `admin@gamerdry.local` / `admin123`
- Client : `player@gamerdry.local` / `player123`

## API disponible

- `GET /api/home`
- `GET /api/catalog`
- `GET /api/products/{id}`
- `POST /api/auth/login`
- `POST /api/auth/register`
- `GET /api/profile/{id}`
- `PUT /api/profile/{id}`
- `GET /api/orders/{id}`
- `POST /api/orders`
- `POST /api/reviews`
- `GET /api/admin/dashboard`
- `GET /api/admin/clients`
- `GET /api/admin/products`
- `POST /api/admin/products`
- `PUT /api/admin/products`

## Base de donnees

La base SQLite est geree par :

- `database/schema.sql`
- `database/seed.sql`
- `database/gamerdry.sqlite3`
- `database/gamerdry_dump.sql`

Le service `SqliteStore` reconstruit automatiquement la base si le schema attendu est absent ou incoherent.

Pour la presentation et GitHub :

- `database/gamerdry.sqlite3` est versionnee dans le depot
- `database/gamerdry_dump.sql` permet de lire toute la base en texte sur GitHub
- la page `/database-preview` permet de consulter les tables directement dans le navigateur

Sous WAMP, la page de consultation est accessible a l adresse :

```text
http://localhost/MENstruation_site/public/database-preview
```
