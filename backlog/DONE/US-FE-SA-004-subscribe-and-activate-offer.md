# US-FE-SA-004 - Souscrire et activer une offre depuis le dashboard

## User story

En tant que super-admin, je peux souscrire une offre pour un tenant puis activer ses modules afin d'ouvrir son cadre fonctionnel.

## Valeur

Relier le provisioning client au cycle d'activation SaaS depuis un seul espace frontend.

## Dependances

- depend de `US-025`
- depend de `US-026`
- depend de `US-FE-SA-002`
- depend de `US-FE-SA-003`

## Perimetre

- creation d'abonnement
- activation des modules issus de l'abonnement
- affichage des montants et references retournees

## Definition of Ready

- tenant et offre peuvent etre selectionnes depuis l'UI
- contrat API d'abonnement et d'activation confirme
- retours metier et techniques utiles identifies
- emplacement de l'ecran confirme dans `/super-admin/subscriptions`

## Criteres d'acceptation

- un abonnement peut etre cree avec `tenant_id` et `offer_id`
- l'activation des modules est declenchable depuis le dashboard
- les retours backend utiles sont visibles
- le flux ne montre pas les pages internes du tenant

## Verification

- build frontend reussi
- verification manuelle d'une souscription valide
- verification manuelle d'une activation de modules
- verification manuelle de l'affichage des references et montants retournes

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
