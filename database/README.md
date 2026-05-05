# Base de donnees

Ce dossier contient la base de donnees du projet MENstruation sous plusieurs formes.

## Fichiers

- `gamerdry.sqlite3`
  base SQLite complete, directement utilisable par le site Symfony et visible dans le depot GitHub
- `schema.sql`
  structure des tables
- `seed.sql`
  donnees d'initialisation
- `gamerdry_dump.sql`
  export SQL complet et lisible de la base pour consultation sur GitHub

## Pourquoi plusieurs formats

- la base `sqlite3` permet d'executer le site et de partager exactement la meme base entre plusieurs projets
- le dump SQL permet a un professeur ou a un correcteur de lire le contenu sans ouvrir un fichier binaire

## Apercu

La base contient actuellement :

- `2` clients
- `2` commandes
- `12` produits
- `3` avis

## Consultation pendant la presentation

Le site expose aussi une page de lecture :

- `/database-preview`

Cette page affiche les tables et leurs donnees pour la demonstration.
