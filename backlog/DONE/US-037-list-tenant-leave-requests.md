# US-037 - Lister les demandes de conge d'un tenant

## User Story

En tant que RH ou manager, je peux lister les demandes de conge d'un tenant afin de suivre les demandes en attente, approuvees ou rejetees.

## Valeur

Rendre le backlog RH des conges consultable avant d'introduire les decisions d'approbation.

## Dependances

- depend de `US-033`
- prepare `US-038` et `US-039`

## Scope backend

- endpoint API de listing des `leave_requests` d'un tenant
- filtres minimaux par periode, employe et statut
- tri stable pour exploitation RH
- exposition des relations utiles comme employe et type de conge
- test feature de listing filtre des demandes de conge

## Criteres d'acceptation

- les demandes de conge d'un tenant peuvent etre listees
- les filtres par statut et periode sont respectes
- la reponse est exploitable pour un ecran RH de traitement

## Verification

- test feature de listing ajoute
- verification du filtrage, du tri et de l'isolation par tenant
