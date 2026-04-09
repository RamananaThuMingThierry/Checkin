# US-025 - Souscrire une offre pour un tenant

## User Story

En tant que super-admin, je peux souscrire une offre pour un tenant afin d'activer le cadre commercial du client.

## Valeur

Faire le lien entre le provisioning technique du tenant et sa relation commerciale reelle.

## Dependances

- depend de `US-004`
- depend de `US-005`
- depend de `US-006`
- prepare `US-026`

## Scope backend

- endpoint de creation d'un abonnement tenant
- rattachement d'une offre a un tenant
- dates minimales de debut et statut d'abonnement
- rejection claire des references invalides
- test feature nominal et en erreur

## Criteres d'acceptation

- un tenant peut souscrire une offre
- l'abonnement est persiste
- les references invalides sont rejetees clairement

## Verification

- test feature `SubscribeTenantToOfferTest` ajoute pour la souscription nominale
- verification des rejets sur tenant invalide, offre invalide et payload incomplet
