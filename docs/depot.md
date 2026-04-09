# Depot

## Organisation

Le depot est structure en quatre zones principales:

- `backend/`
  logique serveur, metier et acces aux donnees
- `database/`
  schema, seed et base SQLite locale
- `static/`
  interface, styles, scripts et assets
- `docs/`
  documentation projet

## Bonnes pratiques retenues

- separation claire des responsabilites
- fichiers sources ranges par domaine
- documentation incluse dans le projet
- base de donnees versionnee via SQL
- assets separes du code applicatif

## Depot Git

Le projet est initialise comme depot Git local pour assurer:

- le suivi des modifications
- la traçabilite de l evolution du projet
- une meilleure qualite de remise

## Recommandations de depot

Pour garder un bon depot, il faut:

- ignorer les caches Python
- ignorer la base generee si on veut ne versionner que le schema et le seed
- documenter les changements importants
- garder les fichiers techniques et fonctionnels bien separes
