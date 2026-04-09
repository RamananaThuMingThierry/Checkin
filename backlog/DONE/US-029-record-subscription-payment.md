# US-029 - Enregistrer un paiement d'abonnement

## User Story

En tant que systeme, je peux enregistrer un paiement sur une facture d'abonnement afin de mettre a jour l'etat de recouvrement.

## Valeur

Tracer l'encaissement et fiabiliser le statut financier du tenant.

## Dependances

- depend de `US-027`
- depend de `US-028`

## Scope backend

- modele et persistance `payments`
- endpoint API d'enregistrement d'un paiement
- rattachement du paiement a une facture existante
- mise a jour du statut de facture apres paiement
- validation des montants et de la devise
- test feature de paiement et de transition de statut

## Criteres d'acceptation

- un paiement peut etre enregistre sur une facture existante
- le statut de la facture evolue correctement selon le montant regle
- les paiements sont traces avec une reference exploitable

## Verification

- test feature d'enregistrement de paiement ajoute
- verification des transitions `pending`, `partial` et `paid`
