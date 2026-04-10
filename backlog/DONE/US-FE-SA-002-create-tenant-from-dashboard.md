# US-FE-SA-002 - Creer un tenant depuis le dashboard super-admin

## User story

En tant que super-admin, je peux creer un tenant depuis le dashboard afin d'ouvrir un nouvel espace client.

## Valeur

Donner un point d'entree frontend au provisioning commercial sans entrer dans l'administration interne du tenant.

## Dependances

- depend de `US-006`
- depend de `US-FE-SA-001`

## Perimetre

- formulaire React de creation tenant
- affichage du resultat et de l'identifiant genere
- alignement avec l'endpoint backend `POST /api/v1/tenants`

## Definition of Ready

- contrat API `POST /api/v1/tenants` connu
- champs obligatoires et erreurs attendues identifies
- emplacement de l'ecran confirme dans `/super-admin/tenants`
- retour de creation attendu pour les etapes suivantes explicite

## Criteres d'acceptation

- le formulaire permet de creer un tenant valide
- les erreurs API sont visibles
- l'identifiant du tenant cree est affiche pour les flux suivants
- aucun ecran metier tenant n'est expose ici

## Verification

- build frontend reussi
- verification manuelle d'une creation tenant valide
- verification manuelle d'une erreur de validation backend
- verification manuelle de l'affichage de l'identifiant cree

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
