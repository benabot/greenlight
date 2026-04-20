# Audit Greenlight — fix/ui-mobile-1

## Verdict actuel

GO prod avec réserves

La branche ne se contente plus d un audit statique : le cache HTML local a été purgé et requalifié, le front a été revérifié sur les vues cibles, et le reset visuel a été exécuté en runtime via l admin. Le thème sort donc du `NO-GO` strict. En revanche, la réserve reste nette : le périmètre produit demeure large, certaines combinaisons d options ne sont pas encore qualifiées, et il reste du wording de démonstration à nettoyer hors du cœur technique.

## Bloquants prod

- Aucun bloquant runtime front direct n a été confirmé après purge et revalidation locale.
- Le passage en production reste conditionné à une dernière revue éditoriale et à une qualification des combinaisons extrêmes d options visuelles.

## Problèmes majeurs

- Le thème conserve un périmètre large côté SEO, images, performance et outils ; ce n’est pas un bug immédiat, mais c’est un risque produit et maintenance avant ouverture large.
- La doc et l’historique doivent encore être relus après la remise en place de l’onglet Apparence comme hub léger vers le Customizer.
- Les combinaisons extrêmes d’options visuelles restent peu qualifiées.

## Problèmes secondaires

- `patterns/contact.php` garde encore une description historique trompeuse côté pattern.
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

- Les combinaisons extrêmes `header_sticky + tagline longue + hero image + menu profondeur 2` ne sont pas couvertes.
- Le nettoyage éditorial complet du contenu de démonstration n est pas fait ; seule la partie la plus gênante pour la recette a été retirée.

### Ce qui reste bloquant

- Il ne reste plus de bloquant runtime avéré sur le front local après purge et reset réel.
- Les réserves sont désormais surtout produit : périmètre admin large, wording de démo résiduel, cas extrêmes encore peu testés.

## Décisions à prendre

- Requalifier le verdict en `GO prod avec réserves`, pas en `prod-ready` sans nuance.
- Conserver l’onglet Apparence comme hub léger vers le Customizer avec reset visuel limité aux options d’apparence.
- Reconfirmer plus tard si le périmètre admin Greenlight reste tel quel avant ouverture plus large.

## Plan de remédiation

- Tester le header avec slogan long, hero image et menu avec sous-niveaux.
- Nettoyer les derniers wording trompeurs et figer une doc de reprise simple pour le reset visuel.

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

### Limites d environnement

- `wp-cli` appelé via le runtime Homebrew reste en faux négatif local avec une erreur de connexion DB.
- Le même WordPress chargé par le PHP MAMP (`/Applications/MAMP/bin/php/php8.2.26/bin/php`) fonctionne correctement pour lire et modifier les options runtime.
- La validation a donc été faite avec le runtime PHP cohérent avec l instance HTTP, et non avec le CLI Homebrew.

### Impact sur le verdict prod

- Les faux négatifs liés au cache HTML local et au mauvais runtime CLI sont levés.
- Le thème passe de `NO-GO prod` à `GO prod avec réserves`.
- Les réserves restantes ne sont plus des pannes runtime prouvées, mais des sujets de périmètre produit et de finition éditoriale.
