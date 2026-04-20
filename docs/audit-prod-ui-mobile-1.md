# Audit Greenlight — fix/ui-mobile-1

## Verdict actuel

NO-GO prod

La branche corrige deux écarts concrets : le shell admin retrouve un point d’entrée Apparence cohérent avec un reset visuel sûr, et le header mobile ne dépend plus d’un disclosure plein largeur qui cassait l’alignement du branding. En revanche, la mise en prod reste prématurée tant que le rendu front n’est pas revalidé de bout en bout sur une vraie matrice mobile / tablette / desktop.

## Bloquants prod

- Validation front réelle encore incomplète sur l’instance WordPress cible : smoke tests OK, HTML OK, mais pas encore de recette visuelle finale reproductible sur home, archive, single et page.
- Header mobile corrigé dans le code, mais encore à requalifier sur cas réels avec header sticky, slogan long, hero image et menus de profondeur 2.

## Problèmes majeurs

- Le thème conserve un périmètre large côté SEO, images, performance et outils ; ce n’est pas un bug immédiat, mais c’est un risque produit et maintenance avant ouverture large.
- La doc et l’historique doivent encore être relus après la remise en place de l’onglet Apparence comme hub léger vers le Customizer.
- Les combinaisons extrêmes d’options visuelles restent peu qualifiées.

## Problèmes secondaires

- `patterns/contact.php` garde encore une description historique trompeuse côté pattern.
- La validation visuelle headless locale n’a pas été fiable sur cette instance MAMP ; la recette manuelle doit donc prendre le relais.

## Problèmes mobile spécifiques

- Le bug initial venait du disclosure mobile plein largeur qui faisait tomber le burger sous le branding.
- La branche le corrige en séparant un masthead mobile stable et un panneau menu rendu sous la ligne haute.
- Les tests statiques couvrent maintenant la présence du masthead, du disclosure natif et du reset visuel.

## Décisions à prendre

- Garder le verdict `NO-GO prod` tant que la recette front complète n’est pas faite.
- Conserver l’onglet Apparence comme hub léger vers le Customizer avec reset visuel limité aux options d’apparence.
- Reconfirmer plus tard si le périmètre admin Greenlight reste tel quel avant ouverture plus large.

## Plan de remédiation

- Valider visuellement `http://localhost:8888/greenlight/` sur mobile étroit, mobile standard, tablette et desktop.
- Tester le header avec slogan long, hero image et menu avec sous-niveaux.
- Nettoyer les derniers wording trompeurs et figer une doc de reprise simple pour le reset visuel.
