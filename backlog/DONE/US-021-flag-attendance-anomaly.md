# US-021 - Signaler une anomalie de pointage

## User Story

En tant que systeme, je peux signaler une anomalie de pointage afin de faciliter le controle RH.

## Valeur

Rendre les ecarts visibles rapidement pour traitement operationnel.

## Dependances

- depend de `US-020`

## Scope backend

- endpoint API de listing des anomalies d'une journee consolidee
- detection d'anomalies a partir des `attendance_records` consolides
- types explicites pour retard, sequence incomplete, pauses desequilibrees et absence d'affectation horaire
- filtrage strict par tenant et par date
- test feature de detection et de validation

## Criteres d'acceptation

- une anomalie peut etre detectee sur une journee consolidee
- le type d'anomalie est explicite
- l'information est exploitable par les futurs ecrans RH

## Verification

- test feature `FlagAttendanceAnomalyTest` ajoute pour la detection des anomalies consolidees
- verification du filtrage par tenant, de l'absence d'anomalie sur un record sain et de la validation de la date
