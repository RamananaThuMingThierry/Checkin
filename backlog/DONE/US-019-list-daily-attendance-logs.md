# US-019 - Lister les scans bruts d'une journee

## User Story

En tant qu'admin tenant, je peux consulter les scans bruts d'une journee afin de verifier les evenements captures avant consolidation.

## Valeur

Donner de la visibilite operationnelle sur la source brute du pointage.

## Dependances

- depend de `US-016`
- prepare `US-020`

## Scope backend

- endpoint API de listing quotidien des scans pour un tenant
- filtre obligatoire par date au format `Y-m-d`
- chargement des relations de base `device` et `employee`
- tri chronologique des scans de la journee
- test feature de listing et de validation

## Criteres d'acceptation

- les scans d'une journee peuvent etre listes
- le filtrage par tenant est respecte
- les informations de base du scan sont visibles

## Verification

- test feature `ListDailyAttendanceLogsTest` ajoute pour le listing quotidien
- verification du filtrage par tenant et de la validation du filtre date
