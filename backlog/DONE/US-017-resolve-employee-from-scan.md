# US-017 - Identifier l'employe depuis un badge ou un identifiant

## User Story

En tant que systeme, je peux identifier l'employe concerne par un scan afin de rattacher correctement le pointage brut.

## Valeur

Transformer un evenement brut en donnee exploitable pour la presence journaliere.

## Dependances

- depend de `US-010`
- depend de `US-016`
- prepare `US-020`

## Scope backend

- endpoint API de resolution d'un employe sur un scan existant
- recherche de l'employe dans le tenant du scan via `badge_uid` ou `employee_code`
- possibilite d'utiliser l'identifiant fourni ou celui deja stocke sur le scan
- mise a jour de `attendance_logs.employee_id` quand la resolution reussit
- erreur explicite quand le scan ou l'identifiant ne permet pas la resolution

## Criteres d'acceptation

- un scan peut etre rattache a un employe via un identifiant reconnu
- un employe d'un autre tenant ne peut pas etre resolu
- une identification impossible est traitee explicitement

## Verification

- test feature `ResolveEmployeeFromScanTest` ajoute pour la resolution via badge et via identifiant fourni
- verification des rejets sur scan inconnu, employe d'un autre tenant et identifiant introuvable
