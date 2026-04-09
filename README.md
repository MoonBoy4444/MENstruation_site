# MENstruation

Application lourde locale de boutique e-commerce pour protections adultes pensees pour les longues sessions gaming.

## Objectif

MENstruation propose une experience de boutique complete:

- catalogue produits avec categories, variantes et avis
- compte client avec inscription et connexion
- panier, paiement, livraison et suivi de commande
- back-office administrateur pour piloter le catalogue
- base de donnees SQLite conforme au besoin metier

## Stack technique

- Backend: Python standard library
- Base de donnees: SQLite
- Frontend: HTML, CSS, JavaScript modulaire
- Architecture: MVVM

## Fonctionnalites couvertes

- Base de donnees relationnelle avec schema et jeu de donnees
- Connexion et inscription client
- Profil client et adresses
- Catalogue avec recherche, filtres et fiches produits
- Panier et validation de commande
- Historique et suivi de commande jusqu a la livraison
- Avis produits lies au compte client
- Back-office administrateur

## Lancer l application

```bash
python3 app.py
```

Puis ouvre dans le navigateur l URL affichee dans le terminal, par exemple:

```text
http://127.0.0.1:8002
```

Le serveur essaie `8000`, puis les ports suivants jusqu a trouver un port libre.

## Comptes de demonstration

- Admin: `admin@gamerdry.local` / `admin123`
- Client: `player@gamerdry.local` / `player123`

## Structure du projet

```text
.
|-- app.py
|-- backend/
|   |-- config.py
|   |-- database.py
|   |-- repositories.py
|   |-- server.py
|   `-- services.py
|-- database/
|   |-- gamerdry.sqlite3
|   |-- schema.sql
|   `-- seed.sql
|-- docs/
|   |-- architecture.md
|   |-- depot.md
|   |-- documentation-technique.md
|   `-- traceabilite.md
`-- static/
    |-- assets/
    |-- index.html
    |-- js/
    `-- styles.css
```

## Documentation disponible

- [architecture.md](/Users/user/Desktop/MENstruations/docs/architecture.md)
- [documentation-technique.md](/Users/user/Desktop/MENstruations/docs/documentation-technique.md)
- [traceabilite.md](/Users/user/Desktop/MENstruations/docs/traceabilite.md)
- [depot.md](/Users/user/Desktop/MENstruations/docs/depot.md)

## Base de donnees

Le modele metier repose sur les tables suivantes:

- `client`
- `typeclient`
- `adresse`
- `possede`
- `paiement`
- `typeproduits`
- `produits`
- `commande`
- `lignecommande`
- `livraison`
- `avis`

Le schema est defini dans [schema.sql](/Users/user/Desktop/MENstruations/database/schema.sql) et les donnees de demonstration dans [seed.sql](/Users/user/Desktop/MENstruations/database/seed.sql).

## Notes

- Les mots de passe sont stockes en hash SHA-256 pour la demonstration.
- Les visuels produits sont integres localement dans `static/assets/`.
- Le projet est organise pour rester simple a lancer sans Node.js ni build frontend.
