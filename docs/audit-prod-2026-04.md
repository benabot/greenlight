# Audit production Greenlight — 2026-04-18

## Verdict
NO-GO prod

## Pourquoi
- formulaires publics non fonctionnels
- admin performance invalide
- navigation mobile non accessible au clavier
- incohérences de langue front/admin
- périmètre thème trop large pour un thème seul
- documentation plus optimiste que l’état réel du code

## Bloquants
- formulaires newsletter home/single non branchés
- pattern contact non branché
- handlers `admin_post` / `admin_post_nopriv` absents
- onglet Performance avec formulaires imbriqués
- CTA header pouvant pointer vers `#newsletter` sans cible réelle
- menu burger mobile non accessible

## Décisions à prendre
- garder ou retirer les formulaires publics
- garder ou extraire les briques “plugin-like”
- conserver ou réduire le périmètre admin Greenlight

## Statut projet révisé
préproduction avancée, non prête pour une mise en production sans remédiation

## Fichiers à corriger en priorité
- `home.php`
- `single.php`
- `patterns/contact.php`
- `header.php`
- `assets/css/blocks/navigation.css`
- `inc/admin.php`
- `functions.php`
- `README.md`
