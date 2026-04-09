# US-030 - Lister les presences consolidees d'une journee

## User Story

En tant que RH ou manager, je peux lister les presences consolidees d'une journee afin de consulter le resultat exploitable du pointage.

## Valeur

Passer du scan technique a une vue metier directement lisible pour le suivi quotidien.

## Dependances

- depend de `US-020`
- depend de `US-021`
- prepare `US-031`

## Scope backend

- endpoint API de listing des `attendance_records` d'une journee
- filtrage par tenant, date, agence ou departement si disponible
- exposition des champs consolides utiles au suivi RH
- tri stable pour consultation et futur export
- test feature de listing des presences consolidees

## Criteres d'acceptation

- les presences consolidees d'une journee peuvent etre listees
- les donnees exposees sont directement exploitables par RH
- le filtrage respecte strictement le tenant et la date demandee

## Verification

- test feature de listing des `attendance_records` ajoute
- verification du filtrage et de la structure de reponse
