# US-FE-001 - Poser le shell frontend React et le login

## User Story

En tant qu'utilisateur plateforme ou tenant, je peux me connecter et ouvrir un shell frontend React afin d'acceder aux ecrans metier.

## Valeur

Donner une base navigable et stable au frontend sans melanger l'authentification, les layouts et les pages metier.

## Dependances

- depend de `US-022`
- depend de `US-023`

## Scope frontend

- structure `resources/js` par couches (`api`, `components`, `data`, `hooks`, `layouts`, `pages`, `routes`, `utils`)
- page de login React
- shell dashboard avec navigation
- persistance locale du token et rechargement du profil courant

## Criteres d'acceptation

- un utilisateur peut se connecter depuis le frontend React
- le frontend conserve la session locale
- les pages dashboard sont accessibles via une navigation claire

## Verification

- build frontend reussi
- verification manuelle du login et de l'ouverture du dashboard
