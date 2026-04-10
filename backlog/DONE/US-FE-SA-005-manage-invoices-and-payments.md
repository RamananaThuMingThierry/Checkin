# US-FE-SA-005 - Gerer les factures et paiements du tenant

## User story

En tant que super-admin, je peux generer une facture, lister les factures d'un tenant et enregistrer un paiement afin de suivre le cycle financier.

## Valeur

Donner un poste de pilotage billing au super-admin sans dependre d'outils externes.

## Dependances

- depend de `US-027`
- depend de `US-028`
- depend de `US-029`
- depend de `US-FE-SA-004`

## Perimetre

- generation de facture depuis un abonnement
- listing des factures d'un tenant
- enregistrement d'un paiement sur une facture

## Definition of Ready

- endpoints facture, listing et paiement confirms
- donnees minimales a saisir pour le paiement identifiees
- formats d'etat et de montant a afficher identifies
- emplacement de l'ecran confirme dans `/super-admin/invoices`

## Criteres d'acceptation

- une facture peut etre generee depuis le dashboard
- le listing d'un tenant est consultable
- un paiement peut etre enregistre avec devise et montant
- les erreurs backend sont visibles dans l'interface

## Verification

- build frontend reussi
- verification manuelle de generation de facture
- verification manuelle du listing d'un tenant
- verification manuelle d'enregistrement d'un paiement
- verification manuelle des erreurs backend visibles

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
