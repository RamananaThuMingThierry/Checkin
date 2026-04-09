# US-028 - Lister les factures d'un tenant

## User Story

En tant qu'admin tenant ou super-admin, je peux lister les factures d'un tenant afin de suivre son historique de facturation.

## Valeur

Donner de la visibilite sur les montants emis, les echeances et les statuts de paiement.

## Dependances

- depend de `US-027`
- prepare le futur frontend de billing

## Scope backend

- endpoint API de listing des factures d'un tenant
- filtrage par tenant et tri chronologique
- exposition des informations utiles de facture et de souscription
- gestion du cas ou le tenant est inconnu
- test feature de listing

## Criteres d'acceptation

- un tenant peut recuperer l'historique de ses factures
- les factures sont triees de maniere exploitable
- les informations exposees permettent un suivi billing simple

## Verification

- test feature de listing des factures ajoute
- verification du filtrage strict par tenant et du tri attendu
