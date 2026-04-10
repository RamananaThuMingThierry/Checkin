# US-FE-TA-002 - Connecter le workflow des demandes de conge cote tenant

## User story

En tant que RH ou manager, je peux traiter les demandes de conge depuis le shell tenant afin d'approuver ou rejeter sans quitter le dashboard.

## Valeur

Rendre exploitable le workflow RH cote tenant avec un ecran reellement connecte au backend.

## Dependances

- depend de `US-037`
- depend de `US-038`
- depend de `US-039`
- depend de `US-FE-TA-001`

## Perimetre

- listing des demandes de conge
- filtres de periode et statut
- action d'approbation
- action de rejet avec motif obligatoire
- affichage des retours succes et erreur

## Definition of Ready

- endpoints de listing, approbation et rejet confirmes
- format des filtres RH connu
- regles de rejet avec motif obligatoire confirmees
- emplacement de l'ecran valide dans `/tenant-dashboard/leaves`

## Criteres d'acceptation

- les demandes de conge sont visibles dans le shell tenant
- une demande peut etre approuvee depuis l'UI
- une demande peut etre rejetee avec motif obligatoire
- les retours API sont visibles sans rechargement manuel

## Verification

- build frontend reussi
- verification manuelle du listing
- verification manuelle d'une approbation
- verification manuelle d'un rejet avec motif

## Definition of Done

- implementation frontend faite
- verification effectuee
- documentation backlog maintenue
- criteres QA explicites et verifies
