# US-027 - Generer une facture d'abonnement

## User Story

En tant que systeme, je peux generer une facture a partir d'un abonnement actif afin de materialiser le montant a payer.

## Valeur

Rendre la souscription facturable et tracable dans le cycle SaaS.

## Dependances

- depend de `US-025`
- depend de `US-026`
- prepare `US-028` et `US-029`

## Scope backend

- modele et persistance `invoices`
- generation d'une facture depuis une souscription active
- calcul et copie des montants de l'abonnement vers la facture
- statut initial de facture coherent
- endpoint API de generation manuelle
- test feature de creation et de validation

## Criteres d'acceptation

- une facture peut etre generee depuis une souscription active
- les montants et la devise sont copies de maniere fiable
- une facture contient un numero unique et un statut initial exploitable

## Verification

- test feature de generation de facture ajoute
- verification du calcul des donnees facturees et de l'unicite du numero
