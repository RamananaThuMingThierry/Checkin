# US-020 - Consolider une journee de presence

## User Story

En tant que systeme, je peux consolider les scans bruts d'une journee afin de produire un enregistrement de presence exploitable.

## Valeur

Transformer des evenements bruts en donnees RH utiles pour le suivi journalier.

## Dependances

- depend de `US-016`
- depend de `US-017`
- depend de `US-018`
- depend de `US-013`
- prepare `US-021`

## Scope backend

- endpoint API de consolidation journaliere pour un tenant
- regroupement des scans `success` resolus par employe et par jour
- calcul de `check_in_time`, `check_out_time`, `break_minutes`, `worked_minutes`, `late_minutes` et `overtime_minutes`
- persistance dans `attendance_records` avec statut et notes d'incoherence
- test feature de consolidation nominale et incomplete

## Criteres d'acceptation

- les scans d'une journee peuvent etre consolides
- une entree et une sortie journalieres peuvent etre determinees
- les incoherences sont remontees explicitement

## Verification

- test feature `ConsolidateDailyAttendanceTest` ajoute pour la consolidation nominale et incomplete
- verification de l'exclusion des scans rejetes ou non resolus et de la validation de la date
