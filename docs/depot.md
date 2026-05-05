# Depot

## Organisation

Le depot est maintenant structure autour de Symfony :

- `public/` pour l'entree web et les assets
- `src/` pour le code PHP
- `database/` pour SQLite
- `config/` pour la configuration Symfony
- `docs/` pour la documentation

## Bonnes pratiques retenues

- un seul backend officiel : PHP / Symfony
- une base SQLite embarquee avec schema et seed versionnes
- une separation nette entre controleurs, services et infrastructure
- une documentation de lancement adaptee a Windows et Linux

## Hygiene du depot

Les elements techniques a ignorer sont :

- `.venv/`
- `__pycache__/`
- `*.pyc`
- `var/`
- `vendor/`
