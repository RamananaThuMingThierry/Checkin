# US-FE-SA-003 - Gerer les offres depuis le dashboard super-admin

## User story

En tant que super-admin, je peux creer une offre et lui rattacher des modules afin de piloter le catalogue commercial.

## Valeur

Rendre visible dans le frontend le catalogue deja disponible en backend.

## Dependances

- depend de `US-004`
- depend de `US-005`
- depend de `US-024`
- depend de `US-FE-SA-001`

## Perimetre

- formulaire de creation d'offre
- formulaire de rattachement d'un module a une offre
- affichage du dernier resultat de creation et de mise a jour

## Definition of Ready

- endpoints offre et modules confirms
- format des listes ou selections de modules connu
- retours backend a afficher identifies
- emplacement de l'ecran confirme dans `/super-admin/offers`

## Criteres d'acceptation

- une offre peut etre creee depuis le dashboard
- un module peut etre rattache a une offre existante
- les erreurs backend sont restituees
- les resultats recents sont visibles dans l'ecran

## Verification

- build frontend reussi
- verification manuelle de creation d'offre
- verification manuelle du rattachement d'un module
- verification manuelle des erreurs backend et des retours affiches

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
