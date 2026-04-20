# Audit Greenlight — fix/ui-mobile-1

## Verdict actuel

GO prod avec réserves

La branche ne se contente plus d un audit statique : le cache HTML local a été purgé et requalifié, le front a été revérifié sur les vues cibles, le reset visuel a été exécuté en runtime via l admin, et les combinaisons extrêmes d apparence ont été rejouées sur instance locale réelle. Le thème ne retombe pas sous `NO-GO`. En revanche, la réserve reste nette : le périmètre produit demeure large, un vrai bug d offset sticky a dû être corrigé sur les headers avec tagline longue, et il reste du wording de démonstration à nettoyer hors du cœur technique.

## Bloquants prod

- Aucun bloquant runtime front direct n a été confirmé après purge et revalidation locale.
- Le passage en production reste surtout conditionné à des arbitrages produit et à une dernière revue éditoriale, pas à une panne front prouvée.

## Problèmes majeurs

- Le thème conserve un périmètre large côté SEO, images, performance et outils ; ce n’est pas un bug immédiat, mais c’est un risque produit et maintenance avant ouverture large.
- La doc et l’historique doivent encore être relus après la remise en place de l’onglet Apparence comme hub léger vers le Customizer.
- Les combinaisons extrêmes du header sont désormais mieux qualifiées, mais le bug d offset sticky découvert montre qu une recette runtime reste indispensable dès qu une option allonge réellement le header.

## Problèmes secondaires

- `wp-cli` via le runtime Homebrew reste trompeur en local avec une erreur DB, alors que le même WordPress chargé via le PHP MAMP fonctionne correctement.

## Problèmes mobile spécifiques

- Le bug initial venait du disclosure mobile plein largeur qui faisait tomber le burger sous le branding.
- La recette réelle a confirmé un second défaut : une fois ouvert, le panneau mobile comprimait le branding et écrasait le slogan.
- La branche le corrige en ancrant le panneau sous le burger, sans recomposer la ligne haute du header.
- Les tests statiques couvrent maintenant la présence du masthead, du disclosure natif, du reset visuel et la minification qui doit préserver l offset sticky.

## Validation front réelle

### Ce qui a été testé

- HTML servi via `curl` sur `home`, `archive`, `single`, `page`, `search`, `404`.
- Recette Playwright locale sur session fraîche, sans réutiliser le cache navigateur.
- Home en `320x740`, `390x844`, `768x1024`, `1440x1100`.
- Archive, single, page, search et 404 en mobile standard ; archive et home en desktop.
- Navigation clavier sur le header mobile : `Tab` jusqu au burger, `Enter` pour ouvrir, `Tab` dans le menu mobile.
- Smoke tests PHP : `mobile-nav-accessibility`, `desktop-nav-visibility`, `mobile-header-masthead`, `header-cta`, `appearance-reset`, `assets-deployment-build`, `sticky-header-offset-minify`, `front-page-hero-cta`.

### Ce qui est validé

- Desktop : la navigation est de nouveau visible immédiatement.
- Mobile : le burger est aligné sur la ligne haute avec le branding.
- Mobile : le menu s ouvre au clavier et le focus entre bien dans la navigation mobile.
- Le panneau mobile s affiche sous le burger, sans écraser le slogan ni casser le hero.
- Les vues sans hero ne remontent plus sous le header sticky après correction du minifier CSS.
- Aucun lien mort n a été relevé dans le header sur les vues testées.

### Ce qui ne l est pas

- Le nettoyage éditorial complet du contenu de démonstration n est pas fait ; seule la partie la plus gênante pour la recette a été retirée.

### Ce qui reste bloquant

- Il ne reste plus de bloquant runtime avéré sur le front local après purge et reset réel.
- Les réserves sont désormais surtout produit : périmètre admin large et wording de démo encore partiel hors du cœur technique.

## Qualification des combinaisons extrêmes

- `header_sticky=0` + nav inline ; vue `front-page/home` ; viewport `390x844` ; aucun chevauchement, hero lisible ; statut `OK` ; correction : aucune.
- `header_sticky=1` + `nav_style=burger` + tagline longue ; vue `front-page/home` ; viewport `320x740` ; collision initiale reproduite entre header plus haut et contenu ; statut `OK après correction` ; correction : classe `site-header--with-tagline` dans [header.php](/Users/benoitabot/Sites/greenlight/greenlight/header.php) et offset sticky adaptatif dans [style.css](/Users/benoitabot/Sites/greenlight/greenlight/style.css).
- `header_sticky=1` + `nav_style=burger` + tagline longue + sous-menu profondeur 2 ; vue `front-page/home` ; viewports `320x740` et `768x1024` ; ouverture clavier au `Space`, panneau dans le viewport, sous-menus visibles en mobile, pas de collision burger/tagline ; statut `OK` ; correction : aucune après fix d offset.
- `header_sticky=1` + `nav_style=burger` + tagline longue + hero image + overlay fort + `header_opacity=35` + hero `100vh` ; vue `front-page/home` ; viewports `390x844` et `1440x1100` ; texte hero sous le header, menu mobile exploitable, nav desktop toujours visible ; statut `OK` ; correction : aucune après fix d offset.
- `header_sticky=1` + `nav_style=burger` + tagline longue sur vues sans hero ; vues `archive`, `single`, `page`, `search`, `404` ; viewport `390x844` ; plus aucun titre sous le header collant ; statut `OK après correction` ; correction : même correctif d offset sticky.
- reset visuel après mutation extrême ; admin `Greenlight > Apparence` + front anonyme ; runtime HTTP réel ; redirect `appearance_reset=success`, retour front en `nav-style-inline`, header non collant, hero image retirée ; statut `OK` ; correction : aucune sur cette branche, seulement revalidation.

## Décisions à prendre

- Requalifier le verdict en `GO prod avec réserves`, pas en `prod-ready` sans nuance.
- Conserver l’onglet Apparence comme hub léger vers le Customizer avec reset visuel limité aux options d’apparence.
- Reconfirmer plus tard si le périmètre admin Greenlight reste tel quel avant ouverture plus large.

## Plan de remédiation

- Nettoyer les derniers wording trompeurs.
- Garder une recette locale fondée sur HTTP réel + PHP MAMP tant que `wp-cli` Homebrew reste incohérent.

## Validation runtime locale

### Cache purgé ou non

- Cache HTML Greenlight identifié dans `wp-content/cache/greenlight/*.html`.
- Purge réalisée proprement via `greenlight_purge_page_cache()` chargé avec le PHP MAMP, puis recontrôlée sur le filesystem.
- Le cache est bien régénéré après une requête anonyme sur la home.

### Vues revalidées

- `home`, `archive`, `single`, `page`, `search`, `404` revalidées en HTTP réel.
- `home` revalidée après mutation visuelle, puis après reset visuel.
- `404` revalidée avec `header_sticky=1` pour confirmer l offset sticky avec le CSS minifié servi.

### Reset visuel validé ou non

- Oui, validé en runtime admin/front.
- Mutation visuelle appliquée avant reset : couleur de header custom et titre hero custom.
- Reset exécuté via l admin Greenlight avec case de confirmation, nonce et message de succès.
- Après correction de la sanitize callback, les defaults réels sont bien restaurés, y compris les booléens à `0` (`header_sticky`, `show_tagline`, `hero_cta_enabled`, `hero_cta2_enabled`).
- Les options non visuelles contrôlées (`greenlight_performance_options`, `greenlight_redirects`) restent inchangées.
- Le reset a aussi été rejoué après une combinaison extrême `sticky + burger + tagline longue + hero image + overlay + 100vh` : le POST admin renvoie bien `appearance_reset=success` et le front HTTP repasse en nav inline non collante, sans image hero.

### Limites d environnement

- `wp-cli` appelé via le runtime Homebrew reste en faux négatif local avec une erreur de connexion DB.
- Le même WordPress chargé par le PHP MAMP (`/Applications/MAMP/bin/php/php8.2.26/bin/php`) fonctionne correctement pour lire et modifier les options runtime.
- La validation a donc été faite avec le runtime PHP cohérent avec l instance HTTP, et non avec le CLI Homebrew.
- La lecture des options via le PHP CLI MAMP après un reset admin HTTP est restée incohérente une fois ; dans ce cas, la source de vérité retenue a été le couple admin HTTP + front HTTP réellement servi.

## Fermeture des réserves visibles

- sous-menus desktop profonds : valides en runtime reel sur `front-page` et sur une `page` sans hero, en `1440x1100` et `1728x1200` ; hover et focus clavier qualifiés ; un bug de debordement a droite a ete corrige dans [style.css](/Users/benoitabot/Sites/greenlight/greenlight/style.css).
- wording de demonstration : nettoye sur [patterns/contact.php](/Users/benoitabot/Sites/greenlight/greenlight/patterns/contact.php) et resserre dans [README.md](/Users/benoitabot/Sites/greenlight/greenlight/README.md) pour ne plus laisser croire a un formulaire public natif ou a un statut plus mature que le code.
- runtime local : methode de validation explicitement documentee ; dans cet environnement, la verite vient du front HTTP reel, de l admin HTTP reel et du PHP MAMP, pas de `wp-cli` Homebrew.
- impact sur le verdict final : aucune nouvelle faille structurelle n a ete decouverte ; le statut `GO prod avec réserves` tient toujours et ses reserves sont plus etroites qu avant cette passe.

### Impact sur le verdict prod

- Les faux négatifs liés au cache HTML local et au mauvais runtime CLI sont levés.
- Le thème passe de `NO-GO prod` à `GO prod avec réserves`.
- Les combinaisons extrêmes testées ne font pas retomber le thème sous le seuil `GO prod avec réserves`.
- Les réserves restantes ne sont plus des pannes runtime prouvées, mais des sujets de périmètre produit, de finition éditoriale et de couverture encore partielle sur les sous-menus desktop profonds.
