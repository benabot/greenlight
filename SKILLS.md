# SKILLS.md — Greenlight

Ce document est le contrat operationnel pour tout agent IA ou developpeur qui intervient sur Greenlight.

## Role du theme

Produire un theme WordPress hybride, tres leger, accessible, maintenable et conforme aux standards WordPress actuels, avec Gutenberg, SEO natif et optimisation d images integree.

Le theme s inscrit dans une logique GreenIT: DOM leger, peu de code, peu de poids, peu de scripts, responsive simple a maintenir, et front-end original sans complexite inutile.

## Priorites absolues

### P0

- Ne jamais ajouter de jQuery.
- Ne jamais ajouter de dependance front externe.
- Ne jamais ajouter de JavaScript front sans necessite stricte.
- Ne jamais alourdir le DOM avec des wrappers inutiles.
- Ne jamais contourner l accessibilite pour un gain cosmetique.
- Ne jamais remplacer une API WordPress existante par du custom inutile.
- Ne jamais introduire de logique non maintenable pour aller plus vite.

### P1

- `theme.json` avant CSS custom.
- HTML semantique avant classes decoratives.
- Flexbox ou grid selon le besoin, avec tailles fluides avant breakpoints.
- PHP natif WordPress avant bricolage.
- Simplicite avant abstraction.
- Lisibilite avant astuce.
- Theme le plus leger possible.

## Regles d implementation

### 1. WordPress d abord

Avant d ecrire du code, verifier dans cet ordre:

1. API native WordPress deja disponible;
2. support de theme ou filtre existant;
3. reglage dans `theme.json`;
4. hook leger;
5. code custom minimal.

### 2. Front ultra leger

Le front doit rester fonctionnel avec:

- HTML semantique;
- CSS moderne;
- images optimisees;
- zero JS applicatif.

Le JavaScript n est jamais la reponse par defaut.

### 3. DOM minimal

- DOM leger et structure minimale.

### 4. Responsive sobre

Favoriser:

- `flex`;
- `grid` selon le besoin;
- `flex-wrap`;
- `gap`;
- `clamp()`;
- `min()`, `max()`;
- tailles intrinsiques des medias.

Eviter:

- Breakpoints utilises seulement si la mise en page le demande vraiment.
- corrections visuelles par empilement de media queries;
- composants fragiles dependants d un DOM lourd.
Le responsive doit etre pense pour rester simple a maintenir.

### 5. Accessibilite obligatoire

Verifier toujours:

- skip link;
- focus visible;
- ordre logique des titres;
- labels et noms accessibles;
- navigation clavier;
- contrastes suffisants;
- `aria-*` seulement quand necessaire.

### 6. SEO natif

Le SEO doit rester centralise et sans plugin externe:

- title SEO;
- meta description;
- canonical;
- robots;
- Open Graph;
- Twitter Card;
- JSON-LD;
- sitemap XML;
- champs SEO par contenu;
- reglages globaux si necessaire.

### 7. Images

La gestion d images doit rester native:

- tailles utiles seulement;
- suppression des tailles inutiles;
- WebP si support serveur;
- `loading` et `fetchpriority` coherents;
- preload de l image hero uniquement si justifie.

## Standards de code

### PHP

- Respecter les WordPress Coding Standards.
- Prefixer les fonctions projet avec `greenlight_`.
- Echaper toute sortie.
- Sanitizer toute entree.
- Verifier les droits et les nonces sur les ecritures admin.
- Garder les responsabilites separees par fichier.
- Ne pas disperser la logique SEO ou image dans les templates.

### CSS

- Le design system vit dans `theme.json`.
- `style.css` reste court et cible le strict necessaire.
- Les styles de blocs sont charges de facon conditionnelle quand c est utile.
- Preferer les selecteurs simples et stables.
- Eviter les systemes utilitaires verbeux.

### JS

- JS reserve a l editeur si un besoin reel existe.
- Reutiliser les packages WordPress fournis.
- Pas de build lourd sans justification.
- Pas de dependance npm front pour compenser une structure HTML/CSS insuffisante.

## Repartition cible

### `functions.php`

Doit rester limite a:

- supports du theme;
- enqueue principal;
- includes des fichiers `inc/`;
- hooks d amorcage simples.

### `theme.json`

Contient:

- palette;
- typo;
- espacements;
- layout;
- regles d edition;
- styles globaux.

### `inc/seo*.php`

Gere:

- meta tags;
- champs SEO;
- JSON-LD;
- sitemap;
- page de reglages SEO.

### `inc/images*.php`

Gere:

- tailles d images;
- suppression des tailles inutiles;
- WebP;
- attributs de chargement;
- preload hero;
- page de reglages images.

### `patterns/`

Contient des patterns:

- editables;
- sobres;
- semantiques;
- sans structure inutile.

## Eco-conception GreenIT

- DOM leger et structure minimale.
- Theme le plus leger possible.
- Front-end original mais facilement customisable.
- Responsive pense pour limiter les ruptures de layout.
- Breakpoints utilises seulement si la mise en page le demande vraiment.
- Chaque ajout doit justifier son cout en poids, complexite et maintenabilite.

## Anti-patterns a refuser

- ajout de jQuery;
- ajout d un framework CSS;
- ajout d un slider ou d une interaction front non essentielle;
- multiplication de `div` techniques;
- logique SEO dupliquee dans les templates;
- logique image dupliquee dans les templates;
- sorties non echapees;
- entrees admin sans nonce;
- breakpoints utilises pour masquer un markup mal pense;
- dependance a un DOM fragile pour la mise en page.

## Definition de termine

Une tache n est terminee que si:

- le code respecte `TODO.md`;
- la solution reste plus simple que l alternative custom;
- le theme reste lisible et maintenable;
- l accessibilite de base est preservee;
- les standards WordPress sont respectes;
- aucune dependance externe n a ete introduite.

## Checklist rapide

Avant de livrer, verifier:

- pas de jQuery;
- pas de dependance front externe;
- pas de JS front ajoute sans necessite;
- DOM minimal et semantique;
- `theme.json` utilise avant CSS custom;
- CSS fluide sans inflation inutile;
- sorties echappees;
- entrees sanitisees;
- nonces presentes sur les ecritures;
- SEO centralise et sans plugin;
- images optimisees et chargees correctement;
- compatibilite Gutenberg preservee;
- pas de regression evidente de poids ou de complexite.

## Methode de contribution

Quand tu modifies le theme:

1. lire les contraintes du projet;
2. verifier l API WordPress existante;
3. produire la solution la plus simple qui couvre le besoin;
4. limiter le nombre de fichiers touches;
5. eviter les refactors non demandes;
6. documenter brièvement toute decision non evidente.
