# US-FE-002 - Valider les demandes de conge dans le frontend

## User Story

En tant que RH ou manager, je peux traiter les demandes de conge depuis une interface React afin d'approuver ou rejeter sans passer par des appels API manuels.

## Valeur

Rendre exploitable le workflow RH livre en backend.

## Dependances

- depend de `US-037`
- depend de `US-038`
- depend de `US-039`
- depend de `US-FE-001`

## Scope frontend

- ecran de listing des demandes de conge
- filtres de periode et perimetre
- action d'approbation
- action de rejet avec motif obligatoire

## Criteres d'acceptation

- les demandes en attente sont visibles
- une demande peut etre approuvee depuis l'UI
- une demande peut etre rejetee avec motif depuis l'UI
- les retours succes et erreur sont visibles

## Verification

- build frontend reussi
- verification manuelle du traitement d'une demande
