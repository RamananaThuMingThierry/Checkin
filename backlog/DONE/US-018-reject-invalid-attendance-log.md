# US-018 - Rejeter un scan invalide

## User Story

En tant que systeme, je peux rejeter un scan invalide afin de proteger l'integrite des donnees de pointage.

## Valeur

Eviter de polluer la base de scans avec des evenements incoherents ou non exploitables.

## Dependances

- depend de `US-016`
- prepare `US-020`

## Scope backend

- endpoint API de classement explicite d'un scan invalide
- mapping des motifs de rejet vers `failed`, `duplicate` ou `unauthorized`
- message traceable persiste sur `attendance_logs.message`
- conservation du scan brut avec un statut exploitable par la suite
- test feature nominal et de validation

## Criteres d'acceptation

- un scan sans appareil autorise est rejete
- un scan sans employe resolvable est rejete ou classe explicitement
- les erreurs sont tracables

## Verification

- test feature `RejectInvalidAttendanceLogTest` ajoute pour les motifs unresolved, unauthorized et duplicate
- verification des rejets sur scan inconnu et payload incomplet
